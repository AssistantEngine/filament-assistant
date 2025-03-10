<?php

return [
    // Set the default chat driver class. You can override this in your local config.
    'chat_driver' => \AssistantEngine\Filament\Chat\Driver\DefaultChatDriver::class,
    'conversation_resolver' => \AssistantEngine\Filament\Chat\Resolvers\ConversationOptionResolver::class,
    'context_resolver' => \AssistantEngine\Filament\Chat\Resolvers\ContextResolver::class,
    'run_processor' => \AssistantEngine\Filament\Runs\Services\RunProcessorService::class,

    'default_run_queue' => env('DEFAULT_RUN_QUEUE', 'default'),
    'default_assistant' => env('DEFAULT_ASSISTANT_KEY', 'food-delivery'),

    // Assistants configuration: each assistance is identified by a key.
    // Each assistance has a name, a instruction, and a reference to an LLM connection.
    'assistants' => [
        // Example assistance configuration with key "default"
        'default' => [
            'name'              => 'Genius',
            'description'       => 'Your friendly assistant ready to help with any question.',
            'instruction'       => 'You are a helpful assistant.',
            'llm_connection'    => 'openai', // This should correspond to an entry in the llm_connections section.
            'model'             => 'gpt-4o',
            'registry_meta_mode' => false,
            // List the tool identifiers to load for this assistant.
            'tools'             => ['weather']
        ],
        'food-delivery' => [
            'name'              => 'Frank',
            'description'       => 'Franks here to help to get you a nice meal',
            'instruction'       => 'Your are Frank a funny person who loves to help customers find the right food.',
            'llm_connection'    => 'openai', // This should correspond to an entry in the llm_connections section.
            'model'             => 'gpt-4o',
            'registry_meta_mode' => false,
            // List the tool identifiers to load for this assistant.
            'tools'             => ['pizza', 'burger']
        ],
    ],

    // LLM Connections configuration: each connection is identified by an identifier.
    // Each connection must include an URL and an API key.
    'llm_connections' => [
        // Example LLM connection configuration with identifier "openai"
        'openai' => [
            'url'     => 'https://api.openai.com/v1/',
            'api_key' => env('OPEN_AI_KEY'),
        ]
    ],

    // Registry configuration
    'registry' => [
        'description' => 'Registry where you can control active functions.',
        'presenter'   => function($registry) {
            // This closure receives the open function registry as a parameter.
            // You can customize how the registry is "presented" here.
            return new \AssistantEngine\OpenFunctions\Core\Presenter\RegistryPresenter($registry);
        },
    ],

    // Tools configuration: each tool is identified by a key.
    'tools' => [
        'weather' => [
            'namespace'   => 'weather',
            'description' => 'Function to get informations about the weather.',
            'tool'        => function () {
                return new \AssistantEngine\OpenFunctions\Core\Examples\WeatherOpenFunction();
            },
        ],
        'pizza' => [
            'namespace'   => 'pizza',
            'description' => 'This is a nice pizza place',
            'tool'        => function () {
                $pizza = [
                    'Margherita',
                    'Pepperoni',
                    'Hawaiian',
                    'Veggie',
                    'BBQ Chicken',
                    'Meat Lovers'
                ];
                return new \AssistantEngine\OpenFunctions\Core\Examples\DeliveryOpenFunction($pizza);
            },
        ],
        'burger' => [
            'namespace'   => 'burger',
            'description' => 'This is a nice burger place',
            'tool'        => function () {

                $burgers = [
                    'Classic Burger',
                    'Cheese Burger',
                    'Bacon Burger',
                    'Veggie Burger',
                    'Double Burger'
                ];
                return new \AssistantEngine\OpenFunctions\Core\Examples\DeliveryOpenFunction($burgers);
            },
        ],
    ],

    'button' => [
        'show' => true,
        'options' => [
            'label' => 'Food Delivery',
            'size' => \Filament\Support\Enums\ActionSize::ExtraLarge,
            'color' => \Filament\Support\Colors\Color::Sky,
            'icon' => 'heroicon-o-chat-bubble-bottom-center-text'
        ]
    ],

    // Sidebar configuration
    'sidebar' => [
        // Whether the sidebar is enabled
        'enabled' => true,
        // If set to true, the sidebar will be open by default on load.
        // Using 'open_by_default' instead of 'auto_visible'
        'open_by_default' => false,
        // The width of the sidebar, defined as a CSS dimension.
        // must be an integer
        'width' => 400,
    ],
];
