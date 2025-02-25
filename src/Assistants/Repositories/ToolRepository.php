<?php

namespace AssistantEngine\Filament\Assistants\Repositories;

use AssistantEngine\Filament\Assistants\Models\Tool;

class ToolRepository
{
    /**
     * Retrieve a tool instance by its identifier.
     *
     * @param string $toolIdentifier
     * @return Tool|null
     */
    public function getTool(string $toolIdentifier): ?Tool
    {
        $toolsConfig = config('filament-assistant.tools', []);
        if (isset($toolsConfig[$toolIdentifier])) {
            $toolDefinition = $toolsConfig[$toolIdentifier];
            if (isset($toolDefinition['tool']) && is_callable($toolDefinition['tool'])) {
                $toolInstance = call_user_func($toolDefinition['tool']);
                return new Tool(
                    $toolIdentifier,
                    $toolDefinition['namespace'] ?? '',
                    $toolDefinition['description'] ?? '',
                    $toolInstance
                );
            }
        }
        return null;
    }

    /**
     * Retrieve multiple tools by an array of identifiers.
     *
     * @param array $toolIdentifiers
     * @return array<string, Tool>
     */
    public function getTools(array $toolIdentifiers): array
    {
        $tools = [];
        foreach ($toolIdentifiers as $identifier) {
            $tool = $this->getTool($identifier);
            if ($tool !== null) {
                $tools[$identifier] = $tool;
            }
        }
        return $tools;
    }
}
