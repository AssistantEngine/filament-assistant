# Filament Assistant

The Filament Assistant Plugin by [Assistant Engine](https://www.assistant-engine.com/) makes it very easy to add conversational AI capabilities directly into Laravel Filament projects. It includes a chat sidebar, context resolver and the possibility to connect to 3rd party tools.

## Requirements

- **PHP**: 8.2 or higher
- **Composer**
- **Filament**: (See [Filament Installation Guide](https://filamentphp.com/docs/3.x/panels/installation))
- **Filament Custom Theme**: (See [Installation Guide](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme))
- **OpenAI API Key**: (See [OpenAI Documentation](https://platform.openai.com/docs/api-reference/authentication))

## Installation

You can install Filament Assistant via Composer:

```bash
composer require assistant-engine/filament-assistant
```

After installing the plugin, follow the instructions to create a [custom theme](https://filamentphp.com/docs/3.x/panels/themes#creating-a-custom-theme) and add the following lines to your new theme's `tailwind.config.js`:

```typescript
// resources/css/filament/admin(theme name)/tailwind.config.js
export default {
    content: [
        './vendor/assistant-engine/filament-assistant/resources/**/*.blade.php',
    ]
};
```

As well as enabling the plugin within your panel:

```php
use AssistantEngine\Filament\FilamentAssistantPlugin;

class YourPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugin(FilamentAssistantPlugin::make());

    }
}
```

Now add you *OPEN_AI_KEY* to your .env File

```
OPEN_AI_KEY=your_openai_key
```

Run the migrations, start a queue worker and building the theme:

```bash
php artisan migrate
php artisan queue:work

npm run dev
```

After that you can directly talk to one of the Demo Assistants (eg. Frank) and have a conversation about food delivery :)

![Demo Assistant Example](media/demo-assistant.png)

## Configuration

You can publish the configuration file using the command below:

```bash
php artisan vendor:publish --tag=filament-assistant-config
```

After publishing the configuration, you can find it in `config/assistant-engine.php`:

```php
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
            // List the tool identifiers to load for this assistant.
            'tools'             => ['weather']
        ],
        'food-delivery' => [
            'name'              => 'Frank',
            'description'       => 'Franks here to help to get you a nice meal',
            'instruction'       => 'Your are Frank a funny person who loves to help customers find the right food.',
            'llm_connection'    => 'openai', // This should correspond to an entry in the llm_connections section.
            'model'             => 'gpt-4o',
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

```

Feel free to change the assistants, add new tools and also update the other configuration parameters as needed.

### Conversation Option Resolver

The **Conversation Option Resolver** is used to determine which conversation option should be used when initializing the assistant. It allows you to implement custom logic based on the current page or other factors to control whether an assistant should be displayed and how it should behave.

You can create a custom conversation option resolver by implementing the `ConversationOptionResolverInterface`. This gives you complete control over the behavior, including the ability to determine whether to return a conversation option or not. If you return `null`, no conversation or assistant will be shown on the page.

Example of the built-in Conversation Option Resolver:

```php
namespace AssistantEngine\Filament\Chat\Resolvers;

use AssistantEngine\Filament\Chat\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
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

        if (!auth()->check()) {
            return null;
        }

        return new ConversationOption($assistantKey, auth()->user()->id);
    }
}
```

You can also customize the resolver logic to adapt to different pages or user roles, providing a tailored conversational experience by extending the built-in ConversationOptionResolver or implement the interface on your own.

### ConversationOption Object

The `ConversationOption` object allows you to configure how a conversation is created or retrieved. The available fields include:

```php
namespace AssistantEngine\Filament\Chat\Models\ConversationOption;

// Create a new ConversationOption
$options = new ConversationOption($assistantKey, $userId);

// arbitrary data you want to pass to the llm
$options->additionalRunData = [
    'your_context' => 'data'
]; // default []

// add additional tools for the assistant independent of the configuration
$options->additionalTools = ['weather']; // default []

// arbitrary data without any function
$options->metadata = ['foo' => 'bar']; // default [] 

// if true the next time the conversation is recreated
$options->recreate = false; // default false
```

- **assistantKey** (required): Unique key identifying the assistant.
- **userId** (required): ID of the user associated with the conversation, allowing multiple users to have different conversations with the same assistant.
- **additionalRunData** (optional): Arbitrary data to provide context to the conversation. This context is included with the conversation data sent to the LLM.
- **metadata** (optional): Data intended for the front-end or client application, allowing additional operations based on its content.
- **recreate** (optional): If set to true, recreates the conversation, deactivating the previous one.

> Note: The Filament Assistant will attempt to locate an existing conversation based on the combination of `assistantKey`, `userId`. If a match is found, that conversation will be retrieved; otherwise, a new one will be created.

### Context Resolver

The **Context Resolver** is responsible for resolving context models that are visible on the page and providing them to the assistant. This helps the assistant understand the context of the current page and allows it to access relevant information during the conversation.

![Custom Pages Example](media/context-resolver-2.png)

The default **Context Resolver** (`ContextResolver`) tries to collect models related to the page, such as records or list items, and injects them into the context of the `ConversationOption` object.

Example of a Context Resolver:

```php
<?php

namespace AssistantEngine\Filament\Chat\Resolvers;

use AssistantEngine\Filament\Chat\Contracts\ContextModelInterface;
use AssistantEngine\Filament\Chat\Contracts\ContextResolverInterface;
use Filament\Pages\Page;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Resources\RelationManagers\RelationManager;

class ContextResolver implements ContextResolverInterface
{
    public function resolve(Page $page): array
    {
        $result = [];

        // Collect models directly related to the page's record
        if (isset($page->record)) {
            $this->collectFromRecord($result, $page->record);
        }

        // Collect models for ListRecords page
        if ($page instanceof ListRecords) {
            $this->collectFromListRecordsPage($result, $page);
        }

        // Collect models for ManageRelatedRecords page
        if ($page instanceof ManageRelatedRecords) {
            $this->collectFromManageRelatedRecordsPage($result, $page);
        }

        // Collect models from relation managers
        if (method_exists($page, "getRelationManagers") && !empty($page->getRelationManagers())) {
            $this->collectFromRelationManagers($result, $page);
        }

        return $this->resolveCollectedModels($result);
    }
}
```

The **Context Resolver** automatically gathers information about the page and its related models, enabling the assistant to leverage this information during a conversation.

### Custom Context Resolvers

Sometimes you have pages which are fully custom, and where the standard Context Resolver doesn't get all the models visible to the customer. In this case, you can either implement your own Context Resolver based on the interface, or you can extend it, like in the example below, to add more context. You can extend the Context Resolver and, based on different pages, inject other contexts, like models or the description of the page, to give the LLM even more context about what the user is seeing right now.

Example of a Custom Context Resolver:

```php
<?php

namespace App\Modules\Assistant\Resolvers;

use App\Filament\Resources\ProductResource\Pages\Ideas\IdeaPlanner;
use App\Modules\Product\Models\ProductGoal;
use App\Modules\Product\Models\ProductIdea;
use Filament\Pages\Page;

class ContextResolver extends AssistantEngine\Filament\Chat\Resolvers\ContextResolver
{
    public function resolve(Page $page): array
    {
        $context = parent::resolve($page);

        return match (get_class($page)) {
            IdeaPlanner::class => $this->handleIdeaPlannerPage($page, $context),
            default => $context
        };
    }

    protected function handleIdeaPlannerPage(IdeaPlanner $page, array $context): array
    {
        $context['pageDescription'] = "This page shows a matrix where product goals are the rows and the roadmap phases (now, next, later)"
        . " are the columns. The user can drag and drop the product ideas between different phases and product goals"
        . " The Ideas you find in the context which don't belong to a goal are unassigned";

        $context = array_merge_recursive($context, $this->resolveModels(ProductGoal::class, $page->goals->all()));

        return array_merge_recursive($context, $this->resolveModels(ProductIdea::class, $page->ideas->all()));
    }
}
```

### Custom Model Serialization

The standard resolving mechanism for models is to transform them to arrays. But sometimes you want to have a different model serialization. Maybe you want to hide properties or give the LLM a little bit more context regarding the models it sees. Therefore, another interface exists called **Context Model Interface**, which defines a static function `resolveModels` that you can implement and use to resolve a list of models of the same type.


```php
<?php

namespace AssistantEngine\Filament\Chat\Contracts;

interface ContextModelInterface
{
    public static function resolveModels(array $models): array;
}

```

There is also a trait implementing this interface called **Context Model**, where you can group models from the same class inside a data object and provide the LLM with metadata as well as exclude properties from the model itself. This ensures that sensitive data is not sent to the LLM directly, but you can adjust it to your needs.

```php
<?php

namespace AssistantEngine\Filament\Chat\Traits;

use AssistantEngine\Filament\Chat\Resolvers\ContextModelResolver;

trait ContextModel
{
    public static function getContextMetaData(): array
    {
        return [
            'schema' => self::class
        ];
    }

    public static function getContextExcludes(): array
    {
        return [];
    }

    public static function resolveModels(array $models): array
    {
        $result = [];
        $result['data'] = null;

        if (count($models) > 0) {
            $result['data'] = ContextModelResolver::collection($models)->resolve();
        }

        $result['meta'] = self::getContextMetaData();

        return $result;
    }
}
```

This Trait you can implement in your Model Classes and overwrite the defined methods if needed:

```php
namespace AssistantEngine\Filament\Chat\Contracts\ContextModelInterface;

#[Schema(
    schema: "Product",
    properties: [
        new Property(property: "id", type: "integer"),
        new Property(property: "title", type: "string"),
        new Property(property: "description", type: "string"),
        new Property(property: "created_at", type: "string", format: "date-time"),
        new Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class Product extends Model implements ContextModelInterface
{
    use ContextModel;

    protected $fillable = ['title', 'description', 'integration_settings', 'assistant_overwrites'];

    public static function getContextExcludes(): array
    {
        return ['integration_settings'];
    }

    public static function getContextMetaData(): array
    {
        return ['schema' => 'Product'];
    }
}
```

### Tool Calling

Of course, there's also the flow backwards from the chat to your application, so that the assistant can access your application. All you need to do is expose an API, which can be defined or described by an OpenAPI schema, and create within the Assistant Engine a new tool, and connect your assistant to the tool. Then, the assistant can perform operations on this API (eg. CRUD).

![Tool Calling Example](media/tool-calling.png)

After the message is processed, the page component automatically refreshes so that you can see what the assistant updated for you. If you want, you can also manually listen to the event; just implement a listener on ```ChatComponent::EVENT_RUN_FINISHED``` and then you can process your custom logic.

```php
#[On(ChatComponent::EVENT_RUN_FINISHED)]
public function onRunFinished()
{
    // Handle run finished event
}
```

You can also connect your assistant to other APIs and let the assistant perform tasks for you in other systems or third-party systems, which are also connected to the assistant with the tool. You can learn more about tool usage in the official documentation. You can also connect your local APIs via a tunnel, such as ngrok, to the Assistant Engine and work locally without the need of deploying an api.

## One More Thing

We’ve created more repositories to make working with the Assistant Engine even easier! Check them out:

- **[PHP SDK](https://github.com/AssistantEngine/php-sdk)**: The PHP SDK provides a convenient way to interact with the Assistant Engine API, allowing developers to create and manage conversations, tasks, and messages.
- **[Laravel Assistant](https://github.com/AssistantEngine/laravel-assistant)**: The Laravel integration for adding conversational AI capabilities in your Laravel applications.

> We are a young startup aiming to make it easy for developers to add AI to their applications. We welcome feedback, questions, comments, and contributions. Feel free to contact us at [contact@assistant-engine.com](mailto:contact@assistant-engine.com).

## Contributing

We welcome contributions from the community! Feel free to submit pull requests, open issues, and help us improve the package.

## License

This project is licensed under the MIT License. Please see [License File](LICENSE.md) for more information.
