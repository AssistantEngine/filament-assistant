<?php

namespace AssistantEngine\Filament\Assistants\Repositories;

use AssistantEngine\OpenFunctions\Core\Contracts\MessageListExtensionInterface;
use AssistantEngine\OpenFunctions\Core\Tools\OpenFunctionRegistry;

class RegistryRepository
{
    /**
     * Retrieve the registry description from configuration.
     *
     * @return string
     */
    public function getRegistryDescription(): string
    {
        return config('filament-assistant.registry.description', '');
    }

    /**
     * Resolves and returns the registry presenter using the configured closure.
     *
     * @param OpenFunctionRegistry $registry
     * @return mixed|null
     */
    public function resolveRegistryPresenter(OpenFunctionRegistry $registry): ?MessageListExtensionInterface
    {
        $presenter = config('filament-assistant.registry.presenter');

        if (is_callable($presenter)) {
            return $presenter($registry);
        }

        return null;
    }
}