<?php

namespace AssistantEngine\Filament\Chat\Presenters;

use AssistantEngine\Filament\Assistants\Repositories\AssistantRepository;
use AssistantEngine\Filament\Threads\Models\Message as EloquentMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\UserMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\AssistantMessage;
use AssistantEngine\OpenFunctions\Core\Models\Messages\Content\ToolCall as DomainToolCall;

class MessagePresenter
{
    /**
     * Cache for loaded assistants, keyed by assistant key.
     *
     * @var array
     */
    private static array $assistantCache = [];

    /**
     * Maps an Eloquent message instance to the corresponding domain message.
     *
     * @param EloquentMessage $message
     * @return UserMessage|AssistantMessage|null
     */
    public static function mapToDomainMessage(EloquentMessage $message)
    {
        if ($message->role === EloquentMessage::ROLE_USER) {
            return new UserMessage($message->content);
        }

        if ($message->role === EloquentMessage::ROLE_ASSISTANT) {
            $assistantName = null;

            if (isset($message->runStep->run)) {
                $run = $message->runStep->run;
                $assistantName = self::getAssistantName($run->assistant_key);
            }

            // Construct the assistant message with the assistant name as the second argument.
            $assistantMessage = new AssistantMessage($message->content, $assistantName);

            if ($message->toolCall) {
                $tc = $message->toolCall;
                $domainToolCall = new DomainToolCall(
                    $tc->call_id,
                    $tc->call_function,
                    json_encode($tc->call_arguments)
                );
                $assistantMessage->addToolCall($domainToolCall);
            }

            return $assistantMessage;
        }

        return null;
    }

    public static function getAssistantName($assistantKey): ?string
    {
        // Load the assistant from the repository only once per assistant key.
        if (!isset(self::$assistantCache[$assistantKey])) {
            $assistantRepository = app(AssistantRepository::class);
            try {
                self::$assistantCache[$assistantKey] = $assistantRepository->getAssistantByKey($assistantKey);
            } catch (\Exception $exception) {
                return null;
            }
        }

        return self::$assistantCache[$assistantKey]->name;
    }

    /**
     * Maps all messages from a thread to an array of domain messages.
     *
     * @param \Illuminate\Database\Eloquent\Collection $messages
     * @return array
     */
    public static function getDomainMessages($messages): array
    {
        return $messages->map(function (EloquentMessage $message) {
            return self::mapToDomainMessage($message);
        })->filter()->toArray();
    }
}
