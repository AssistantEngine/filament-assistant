<?php

namespace AssistantEngine\Filament\Contracts;

use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Pages\Page;

interface ConversationOptionResolverInterface
{
    public function resolve(Page $page): ?ConversationOption;
}
