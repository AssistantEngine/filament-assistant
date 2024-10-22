<?php

// config for AssistantEngine/FilamentAssistant
return [
    'filament' => [
        'assistant-button' => [
            'show' => true,
            'trigger' => \AssistantEngine\Filament\Components\AssistantButton::ASSISTANT_BUTTON_TRIGGER_MODAL,
            'options' => [
                'label' => 'Call Assistant',
                'size' => \Filament\Support\Enums\ActionSize::ExtraLarge,
                'color' => \Filament\Support\Colors\Color::Sky,
                'icon' => 'heroicon-o-academic-cap'
            ]
        ],

        'assistant-sidebar' => [
            'render' => false,
            'show-without-trigger' => false
        ],

        'assistant-modal' => [
            'show-tabs' => false,
            'max-visible-tabs' => 7,
            'show-page-component' => false,
            'auto-height' => true,
            'slide-over' => true,
            'heading' => 'Your AI Assistant',
            'max-width' => \Filament\Support\Enums\MaxWidth::ExtraLarge
        ]
    ]
];
