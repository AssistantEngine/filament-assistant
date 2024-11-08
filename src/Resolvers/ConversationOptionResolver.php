<?php

namespace AssistantEngine\Filament\Resolvers;

use AssistantEngine\Filament\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Pages\Page;

class ConversationOptionResolver implements ConversationOptionResolverInterface
{
    public function resolve(Page $page): ?\AssistantEngine\SDK\Models\Options\ConversationOption
    {
        $assistantKey = config('assistant-engine.filament-assistant.conversation-option.assistant-key');

        if (!$assistantKey) {
            throw new \Exception('assistant-key must be set');
        }

        $option = new ConversationOption($assistantKey, [
            'user_id' => auth()->check() ? auth()->user()->id : null
        ]);

        return $option;
    }
}
