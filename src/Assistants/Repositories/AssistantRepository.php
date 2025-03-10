<?php

namespace AssistantEngine\Filament\Assistants\Repositories;

use AssistantEngine\Filament\Assistants\Models\Assistant;
use AssistantEngine\Filament\Assistants\Models\LLMConnection;
use AssistantEngine\Filament\Assistants\Models\Tool;
use Illuminate\Support\Facades\Config;

class AssistantRepository
{

    private ToolRepository $toolRepository;

    public function __construct(ToolRepository $toolRepository)
    {
        $this->toolRepository = $toolRepository;
    }

    /**
     * Retrieve an Assistant model instance based on the assistant key.
     *
     * @param string $assistantKey
     * @return Assistant|null
     * @throws \Exception if the assistant or its LLM connection configuration is not found.
     */
    public function getAssistantByKey(string $assistantKey): ?Assistant
    {
        $assistants = Config::get('filament-assistant.assistants', []);

        if (!array_key_exists($assistantKey, $assistants)) {
            return null;
        }

        $assistantConfig = $assistants[$assistantKey];

        // Verify that the LLM connection is defined for this assistant.
        $llmConnectionKey = $assistantConfig['llm_connection'] ?? null;
        if (!$llmConnectionKey) {
            throw new \Exception("LLM connection not specified for assistant '{$assistantKey}'.");
        }

        $llmConnections = Config::get('filament-assistant.llm_connections', []);
        if (!array_key_exists($llmConnectionKey, $llmConnections)) {
            throw new \Exception("LLM connection with key '{$llmConnectionKey}' not found in configuration.");
        }

        $llmConnectionConfig = $llmConnections[$llmConnectionKey];

        // Create an LLMConnection model instance.
        $llmConnection = new LLMConnection(
            $llmConnectionKey,
            $llmConnectionConfig['url'],
            $llmConnectionConfig['api_key']
        );

        // Load the assistant's tools using the ToolRepository.
        $tools = [];
        if (isset($assistantConfig['tools']) && is_array($assistantConfig['tools'])) {
            $tools = $this->toolRepository->getTools($assistantConfig['tools']);
        }

        // Retrieve registry meta mode flag; default to false if not set.
        $registryMetaMode = $assistantConfig['registry_meta_mode'] ?? false;

        // Create and return the Assistant model with the resolved tools and the new flag.
        return new Assistant(
            $assistantKey,
            $assistantConfig['name'] ?? $assistantKey,
            $assistantConfig['instruction'] ?? '',
            $assistantConfig['description'] ?? '',
            $llmConnection,
            $assistantConfig['model'] ?? 'gpt-4o',
            $tools,
            $registryMetaMode
        );
    }

    /**
     * Retrieve all configured Assistant instances.
     *
     * @return Assistant[]
     */
    public function getAllAssistants(): array
    {
        $assistantsConfig = Config::get('filament-assistant.assistants', []);
        $assistants = [];

        foreach ($assistantsConfig as $assistantKey => $assistantConfig) {
            try {
                $assistant = $this->getAssistantByKey($assistantKey);
                if ($assistant !== null) {
                    $assistants[$assistantKey] = $assistant;
                }
            } catch (\Exception $e) {
                // Optionally log the error and skip this assistant.
                continue;
            }
        }

        return $assistants;
    }
}
