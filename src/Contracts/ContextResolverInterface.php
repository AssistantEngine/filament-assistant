<?php

namespace AssistantEngine\Filament\Contracts;

use Filament\Pages\Page;

interface ContextResolverInterface
{
    public function resolve(Page $page): array;
}
