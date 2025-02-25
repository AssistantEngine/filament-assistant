<?php

namespace AssistantEngine\Filament\Chat\Contracts;

use AssistantEngine\Filament\Chat\Models\ConversationOption;
use Filament\Pages\Page;

interface ConversationOptionResolverInterface
{
    public function resolve(Page $page): ?ConversationOption;
}
