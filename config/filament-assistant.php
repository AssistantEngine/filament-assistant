<?php

// config for AssistantEngine/FilamentAssistant
return [
    'filament-assistant' => [
        'button' => [
            'show' => true,
            'options' => [
                'label' => 'Assistant',
                'size' => \Filament\Support\Enums\ActionSize::ExtraLarge,
                'color' => \Filament\Support\Colors\Color::Sky,
                'icon' => 'heroicon-o-chat-bubble-bottom-center-text'
            ]
        ],

        'conversation-option' => [
            'assistant-key' => env('ASSISTANT_ENGINE_ASSISTANT_KEY'),
            'conversation-resolver' => \AssistantEngine\Filament\Resolvers\ConversationOptionResolver::class,
            'context-resolver' => \AssistantEngine\Filament\Resolvers\ContextResolver::class
        ],

        'sidebar' => [
            'render' => true,
            'width' => 500,
            'show-without-trigger' => false
        ],
    ]
];
