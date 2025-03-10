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
                // Optionally get the presenter callable if it exists.
                $presenter = null;
                if (isset($toolDefinition['presenter']) && is_callable($toolDefinition['presenter'])) {
                    $presenter = $toolDefinition['presenter'];
                }
                // Create a new Tool instance, passing the presenter if available.
                return new Tool(
                    $toolIdentifier,
                    $toolDefinition['namespace'] ?? '',
                    $toolDefinition['description'] ?? '',
                    $toolDefinition['tool'],
                    $presenter
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