<?php

namespace AssistantEngine\Filament\Chat\Contracts;

use Filament\Pages\Page;

interface ContextResolverInterface
{
    public function resolve(Page $page): array;
}
