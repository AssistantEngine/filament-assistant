<?php

namespace AssistantEngine\Filament\Chat\Contracts;

use Filament\Pages\Page;

interface ContextModelInterface
{
    public static function resolveModels(array $models): array;
}
