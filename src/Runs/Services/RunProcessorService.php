<?php

namespace AssistantEngine\Filament\Runs\Services;

use AssistantEngine\Filament\Assistants\Models\Assistant;
use AssistantEngine\Filament\Assistants\Repositories\RegistryRepository;
use AssistantEngine\Filament\Assistants\Repositories\ToolRepository;
use AssistantEngine\Filament\Runs\Contracts\RunProcessorInterface;
use AssistantEngine\Filament\Runs\Models\Run;
use AssistantEngine\Filament\Runs\Models\RunStep;
use AssistantEngine\Filament\Assistants\Repositories\AssistantRepository;
use AssistantEngine\Filament\Threads\Models\Message;
use AssistantEngine\Filament\Threads\Models\Thread;
use AssistantEngine\Filament\Threads\Models\ToolCall;
use AssistantEngine\Filament\Threads\Repositories\MessageRepository;
use AssistantEngine\Filament\Runs\Presenter\LLMPresenter;
use AssistantEngine\OpenFunctions\Core\Contracts\MessageListExtensionInterface;
use AssistantEngine\OpenFunctions\Core\Models\Messages\DeveloperMessage;
use AssistantEngine\OpenFunctions\Core\Tools\OpenFunctionRegistry;
use OpenAI;

class RunProcessorService implements RunProcessorInterface
{
    protected AssistantRepository $assistantRepository;
    protected MessageRepository $messageRepository;
    private ToolRepository $toolRepository;
    protected Run $run;
    protected Thread $thread;
    protected Assistant $assistant;
    protected OpenFunctionRegistry $registry;
    private RegistryRepository $registryRepository;

    public function __construct(
        AssistantRepository $assistantRepository,
        MessageRepository $messageRepository,
        ToolRepository $toolRepository,
        RegistryRepository $registryRepository
    ) {
        $this->assistantRepository = $assistantRepository;
        $this->messageRepository = $messageRepository;
        $this->toolRepository = $toolRepository;
        $this->registryRepository = $registryRepository;
    }

    /**
     * Process the given run with a more separated approach.
     *
     * @param Run $run
     * @throws \Exception if any step fails.
     */
    public function process(Run $run): void
    {
        // 1. Mark run as in progress.
        $this->updateRunStatus($run, Run::STATUS_IN_PROGRESS);

        $this->run = $run;
        $this->thread = $run->thread;
        $this->assistant = $this->assistantRepository->getAssistantByKey($run->assistant_key);

        if (!$this->assistant) {
            throw new \Exception("Assistant not found for key: {$run->assistant_key}");
        }

        // 3. Merge additional tools into the assistant.
        if ($run->additional_tools) {
            $tools = $this->toolRepository->getTools($run->additional_tools);
            $this->assistant->tools = array_merge($this->assistant->tools, $tools);
        }

        $maxIterations = 3;
        $iteration = 0;

        // 2. Iterate until a non-tool call response is received or max iterations reached.
        while ($iteration < $maxIterations) {
            $iteration++;

            $runStep = $this->processRunStep();

            if ($runStep->type === RunStep::TYPE_MESSAGE_CREATION) {
                break;
            }
        }

        // 10. Mark the run as completed.
        $this->updateRunStatus($run, Run::STATUS_COMPLETED);
    }

    protected function processRunStep(): RunStep
    {
        // 4. Prepare messages in OpenAI format.
        $openaiMessages = LLMPresenter::transformThreadMessages($this->thread, $this->assistant);

        $registry = $this->getOpenFunctionRegistry();
        $functions  = $registry->generateFunctionDefinitions();

        $toolExtensions = $this->getExtensions();

        if ($toolExtensions) {
            $openaiMessages->addExtensions($toolExtensions);
        }

        $registryPresenter = $this->registryRepository->resolveRegistryPresenter($registry);

        if ($registryPresenter) {
            $openaiMessages->addExtension($registryPresenter);
        }

        if ($this->run->additional_run_data) {
            $additionalDeveloperMessage = new DeveloperMessage('Additional Context for the Run: ' . PHP_EOL . json_encode($this->run->additional_run_data));

            $openaiMessages->prependMessages([$additionalDeveloperMessage]);
        }

        $payload = [
            'model'     => $this->run->run_settings['model'],
            'messages'  => $openaiMessages->toArray(),
            'tools'     => $functions, // Passing tool definitions here.
        ];

        $response = $this->callLLM($payload);

        $responseArray = $response->toArray();

        // 4. Pick the first choice.
        $choice = $responseArray['choices'][0] ?? null;

        $stepType = ($choice['finish_reason'] === 'tool_calls') ? RunStep::TYPE_TOOL_CALLS : RunStep::TYPE_MESSAGE_CREATION;
        $runStep = $this->logRunStep($stepType, $responseArray, $openaiMessages->toArray(), $functions);

        // 6. If the response is a tool call, execute the tool(s) and accumulate their responses.
        if ($choice['finish_reason'] === 'tool_calls') {
            $toolCalls = $this->handleToolCalls($choice);

            foreach ($toolCalls as $toolCall) {
                $message = $this->messageRepository->addMessage(
                    $this->thread,
                    Message::ROLE_ASSISTANT,
                    null,
                    $toolCall->id
                );
                $message->run_step_id = $runStep->id;
                $message->save();
            }
        } else {
            $assistantContent = $choice['message']['content'];

            $message = $this->messageRepository->addMessage(
                $this->thread,
                Message::ROLE_ASSISTANT,
                $assistantContent
            );
            $message->run_step_id = $runStep->id;
            $message->save();
        }

        $runStep->status = RunStep::STATUS_COMPLETED;
        $runStep->save();

        $this->thread = $this->thread->fresh(['messages']);

        return $runStep;
    }

    protected function callLLM($payload): OpenAI\Responses\Chat\CreateResponse
    {
        // 7. Instantiate the OpenAI client and include the tool definitions.
        $client = OpenAI::factory()
            ->withApiKey($this->assistant->llmConnection->apiKey)
            ->withBaseUri($this->assistant->llmConnection->url)
            ->make();

        return $client->chat()->create($payload);
    }

    protected function logRunStep(string $type, array $rawResponse, array $messageHistory, array $functionDefinitions): RunStep
    {
        // 8. Create a run step to store the raw response and message history.
        return RunStep::create([
            'run_id'                => $this->run->id,
            'type'                  => $type,
            'status'                => RunStep::STATUS_IN_PROGRESS,
            'raw_response'          => $rawResponse,
            'message_history'       => $messageHistory,
            'function_definitions'  => $functionDefinitions,
        ]);
    }


    /**
     * @param array $response
     * @param OpenFunctionRegistry|null $openFunctionRegistry
     *
     * @return ToolCall[]
     *
     * @throws \Exception
     */
    protected function handleToolCalls(array $response): array
    {
        $toolResponses = [];

        if (isset($response['message']['tool_calls'])) {
            foreach ($response['message']['tool_calls'] as $toolCall) {
                $functionCall = $toolCall['function'];
                $namespacedName = $functionCall['name'] ?? null;
                $argumentsJson = $functionCall['arguments'] ?? '{}';
                $arguments = json_decode($argumentsJson, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Optionally log the error and skip this function call.
                    continue;
                }

                if ($namespacedName) {
                    $toolResponse = $this->getOpenFunctionRegistry()
                        ->callMethod($namespacedName, $arguments);

                    $toolCall = ToolCall::create([
                        'call_id' => $toolCall['id'],
                        'call_function' => $namespacedName,
                        'call_arguments' => $arguments,
                        'response_content' => $toolResponse->toArray(),
                        'status' => $toolResponse->isError ? ToolCall::STATUS_ERROR : ToolCall::STATUS_SUCCESS,
                    ]);

                    $toolResponses[] = $toolCall;
                }
            }
        }

        return $toolResponses;
    }

    /**
     * Updates the run status and persists the change.
     *
     * @param Run $run
     * @param string $status
     */
    private function updateRunStatus(Run $run, string $status): void
    {
        $run->status = $status;
        $run->save();
    }

    /**
     * Initializes the OpenFunctionRegistry by registering all tools.
     *
     * @param array $tools List of Tool model instances.
     * @return OpenFunctionRegistry
     */
    private function getOpenFunctionRegistry(): OpenFunctionRegistry
    {
        if (isset($this->registry)) {
            return $this->registry;
        }

        if ($this->assistant->registryMetaMode) {
            $registry = new OpenFunctionRegistry(true, $this->registryRepository->getRegistryDescription());
        } else {
            $registry = new OpenFunctionRegistry();
        }

        foreach ($this->assistant->tools as $tool) {
            // Assumes that each tool has an "instance" property implementing AbstractOpenFunction.
            $openFunction = $tool->resolveInstance($this->run);
            if ($openFunction) {
                // Use the tool's namespace (or fallback to identifier) and description.
                $namespace = $tool->namespace ?: $tool->identifier;
                $registry->registerOpenFunction($namespace, $tool->description, $openFunction);
            }
        }

        $this->registry = $registry;

        return $registry;
    }

    /**
     * @return MessageListExtensionInterface[]
     */
    private function getExtensions()
    {
        $result = [];

        foreach ($this->assistant->tools as $tool) {
            if ($tool->hasPresenter()) {
                $presenter = $tool->resolvePresenter($this->run);

                if ($presenter) {
                    $result[] = $presenter;
                }
            }
        }

        return $result;
    }
}
