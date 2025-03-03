<?php

namespace AssistantEngine\Filament\Chat\Resolvers;

use AssistantEngine\Filament\Chat\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
use AssistantEngine\Filament\Chat\Pages\AssistantChat;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;

class ConversationOptionResolver implements ConversationOptionResolverInterface
{
    public function resolve(Page $page): ?ConversationOption
    {
        $assistantKey = Config::get('filament-assistant.default_assistant');

        if (!$assistantKey) {
            throw new \Exception('assistant-key must be set');
        }

        if ($page instanceof AssistantChat) {
            return null;
        }

        if (!auth()->check()) {
            return null;
        }

        return new ConversationOption($assistantKey, auth()->user()->id);
    }
}
