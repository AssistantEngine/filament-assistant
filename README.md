<div align="center">

![Demo Video](media/filament-assistant.gif)

</div>

# Filament Assistant

**Free & Open Source Version**  
*For more features and a cloud-based assistant experience, check out [Assistant Engine](https://assistant-engine.com/).*

**Filament Assistant** is an AI-powered plugin that seamlessly integrates conversational capabilities into your Laravel Filament projects. Developed by [Assistant Engine](https://assistant-engine.com/), it provides a feature-rich chat sidebar, intelligent context resolution, and effortless integration with popular tools like **Slack**, **Trello**, **Notion**, **Jira Service Desk**, **Bitbucket**, and **GitHub**.

## Key Features:
- ✅ **Multiple Assistants** – Create AI assistants for different use cases.
- ✅ **Tool Calls** – Let your assistant execute actions, retrieve data and integrate with external tools.
- ✅ **Context Awareness** – Pass relevant page data for smarter responses.
- ✅ **Easy Setup** – Configurable & ready to go in minutes.
- ✅ **Privacy First** – Runs entirely locally, ensuring full control over your data.
- ✅ **Flexible AI Integration** – Works with OpenAI & any LLM using the same API format.

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

#### Dark Mode Support

The `Filament Assistant` also supports dark mode based on the [Tailwind Concept](https://tailwindcss.com/docs/dark-mode).

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
            'registry_meta_mode' => false, // See meta mode for details
            // List the tool identifiers to load for this assistant.
            'tools'             => ['weather']
        ],
        'food-delivery' => [
            'name'              => 'Frank',
            'description'       => 'Franks here to help to get you a nice meal',
            'instruction'       => 'Your are Frank a funny person who loves to help customers find the right food.',
            'llm_connection'    => 'openai', // This should correspond to an entry in the llm_connections section.
            'model'             => 'gpt-4o',
            'registry_meta_mode' => false,  // See meta mode for details
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

    // Registry configuration, learn more about the registry in the Core Repository (https://github.com/AssistantEngine/open-functions-core)
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

```

If you need runtime information during tool calling you can also inject the active run into the closure and then you have access to the actual thread and user identifier.

```php
    'tools' => [
        'weather' => [
            'namespace'   => 'weather',
            'description' => 'Function to get informations about the weather.',
            'tool'        => function (\AssistantEngine\Filament\Runs\Models\Run $run) {
                // Now you can access:
                // $thread = $run->thread;
                // $userIdentifier = $thread->user_identifier;
                
                return new \AssistantEngine\OpenFunctions\Core\Examples\WeatherOpenFunction();
            },
        ]
    ]
```

If you want to add a presenter to your tool you can do it by defining the *presenter* key to the tool configuration. The presenter is an optional callable that returns an instance implementing the **MessageListExtensionInterface**. Learn more about *Extensions* in the **[Core Repository](https://github.com/AssistantEngine/open-functions-core?tab=readme-ov-file#message-list-extensions)**.

```php
    'tools' => [
        'weather' => [
            'namespace'   => 'weather',
            'description' => 'Function to get information about the weather.',
            'tool'        => function () {
                return new \AssistantEngine\OpenFunctions\Core\Examples\WeatherOpenFunction();
            },
            'presenter'   => function (\AssistantEngine\Filament\Runs\Models\Run $run) {
                // Return an instance that implements MessageListExtensionInterface, if needed.             
                return new \AssistantEngine\OpenFunctions\Core\Examples\WeatherMessageListExtension();
            },
        ]
    ]
```

If you want to cache your config you need to convert the closures to callables for example create a ConfigFactory Class like

```php
namespace App\Factories;

class FilamentConfigFactory
{
    public static function weather(Run $run)
    {
        return new \AssistantEngine\OpenFunctions\Core\Examples\WeatherOpenFunction();
    }
    
    // ... other methods
}
```

and then convert the closures to callables like

```php
// config/filament-assistant.php
'tools' => [
    'weather' => [
        'namespace'   => 'weather',
        'description' => 'Function to get informations about the weather.',
        'tool'        => [\App\Factories\FilamentConfigFactory::class, 'weather'],
    ],
    // ... other tools
]
```

If you convert all closures to callables you should be able to cache your config with ```php artisan config:cache```

Feel free to change the assistants, add new tools and also update the other configuration parameters as needed.

## Tool Calling

If you want your assistant to access your application, all you need to do is implement the *AbstractOpenFunction* to create a new Tool and add it to your configuration file. Please read also the **[Open Function Repository](https://github.com/AssistantEngine/open-functions-core?tab=readme-ov-file#function-calling)** to learn more about Open Functions.

An example implementation could be:

```php
use AssistantEngine\OpenFunctions\Core\Contracts\AbstractOpenFunction;
use AssistantEngine\OpenFunctions\Core\Models\Responses\TextResponseItem;
use AssistantEngine\OpenFunctions\Core\Helpers\FunctionDefinition;
use AssistantEngine\OpenFunctions\Core\Helpers\Parameter;

class HelloWorldOpenFunction extends AbstractOpenFunction
{
    /**
     * Generate function definitions.
     *
     * This method returns a schema that defines the "helloWorld" function.
     */
    public function generateFunctionDefinitions(): array
    {
        // Create a new function definition for helloWorld.
        $functionDef = new FunctionDefinition(
            'helloWorld',
            'Returns a friendly greeting.'
        );

        // In this simple example, no parameters are required.
        // If parameters were needed, you could add them like this:
        // $functionDef->addParameter(Parameter::string("name")
        //     ->description("Optional name to greet")
        //     ->required());
        
        // Return the function schema as an array.
        return [$functionDef->createFunctionDescription()];
    }

    /**
     * The actual implementation of the function.
     *
     * @return TextResponseItem A text response containing the greeting.
     */
    public function helloWorld()
    {
        return new TextResponseItem("Hello, world!");
    }
}
```

#### Meta Mode

Enable **MetaMode** in the assistant configuration to expose only the meta registry functions, letting the assistant dynamically activate or deactivate additional functions as needed—ideal for when too many functions may overwhelm an LLM call. For more details, see the **[Open Functions Core](https://github.com/AssistantEngine/open-functions-core?tab=readme-ov-file#meta-mode)** repository.

### Available Open Function Implementations

In addition to creating your own Open Functions, there are several ready-to-use implementations available to extend your assistant’s capabilities. Simply add the corresponding tool configuration in your config/filament-assistant.php file to integrate them. Here’s a quick overview:

- **[Memory](https://github.com/AssistantEngine/open-functions-memory)**:  Provides a standardized API for storing, updating, retrieving, and removing conversational memories.
- **[Notion](https://github.com/AssistantEngine/open-functions-notion)**: Connects to your Notion workspace and enables functionalities such as listing databases, retrieving pages, and managing content blocks.
- **[GitHub](https://github.com/AssistantEngine/open-functions-github)**: Integrates with GitHub to allow repository operations like listing branches, reading files, and committing changes.
- **[Bitbucket](https://github.com/AssistantEngine/open-functions-bitbucket)**: Provides an interface similar to GitHub’s, enabling you to interact with Bitbucket repositories to list files, read file contents, and commit modifications.
- **[Trello](https://github.com/AssistantEngine/open-functions-trello)**: Enables interactions with Trello boards, lists, and cards, facilitating project management directly within your assistant.
- **[Slack](https://github.com/AssistantEngine/open-functions-slack)**: Seamlessly connects your assistant to Slack and perform actions like listing channels, posting messages, replying to threads, adding reactions, and retrieving channel history and user profiles.
- **[Jira Service Desk](https://github.com/AssistantEngine/open-functions-jira-service-desk)**: Integrates with Jira Service Desk to interact with service requests—enabling you to create, update, and manage requests (cards), list queues, add comments, transition statuses, and manage priorities.

## Resolvers

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

## Assistant Chat Page

In addition to the built-in chat sidebar, you can add a dedicated Assistant Chat Page in your Filament panel. This page allows you to interact with your assistants directly from a full-page interface. To enable this feature, simply add the chat page to your panel provider.

For example, you can update your panel provider as follows:

```php
use AssistantEngine\Filament\Chat\Components\ChatPage;
use AssistantEngine\Filament\FilamentAssistantPlugin;
use Filament\Panels\Panel;
use Filament\Panels\PanelProvider;

class YourPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugin(FilamentAssistantPlugin::make())
            // Register the dedicated chat page:
           ->pages([
                \AssistantEngine\Filament\Chat\Pages\AssistantChat::class
            ])
    }
}
```

![Assistant Chat Example](media/assistant-chat.png)


## Events

After the message is processed, the page component automatically refreshes so that you can see what the assistant updated for you. If you want, you can also manually listen to the event; just implement a listener on ```ChatComponent::EVENT_RUN_FINISHED``` and then you can process your custom logic.

```php
use AssistantEngine\Filament\Chat\Components\ChatComponent;

#[On(ChatComponent::EVENT_RUN_FINISHED)]
public function onRunFinished($messages)
{
    // Handle run finished event
}
```

## More Repositories

We’ve created more repositories to make AI integration even simpler and more powerful! Check them out:

- **[Open Functions Core](https://github.com/AssistantEngine/open-functions-core)**: Powerful primitives that simplify LLM calling.
- **[Open Functions Actions](https://github.com/AssistantEngine/open-functions-actions)**: Serializes OpenFunctions into ChatGPT actions.

Ready-to-use **OpenFunctions** implementations:
- **[Memory](https://github.com/AssistantEngine/open-functions-memory)**:  Provides a standardized API for storing, updating, retrieving, and removing conversational memories.
- **[Notion](https://github.com/AssistantEngine/open-functions-notion)**: Connects to your Notion workspace and enables functionalities such as listing databases, retrieving pages, and managing content blocks.
- **[GitHub](https://github.com/AssistantEngine/open-functions-github)**: Integrates with GitHub to allow repository operations like listing branches, reading files, and committing changes.
- **[Bitbucket](https://github.com/AssistantEngine/open-functions-bitbucket)**: Provides an interface similar to GitHub’s, enabling you to interact with Bitbucket repositories to list files, read file contents, and commit modifications.
- **[Trello](https://github.com/AssistantEngine/open-functions-trello)**: Enables interactions with Trello boards, lists, and cards, facilitating project management directly within your assistant.
- **[Slack](https://github.com/AssistantEngine/open-functions-slack)**: Seamlessly connects your assistant to Slack and perform actions like listing channels, posting messages, replying to threads, adding reactions, and retrieving channel history and user profiles.
- **[Jira Service Desk](https://github.com/AssistantEngine/open-functions-jira-service-desk)**: Integrates with Jira Service Desk to interact with service requests—enabling you to create, update, and manage requests (cards), list queues, add comments, transition statuses, and manage priorities.

> We are a young startup aiming to make it easy for developers to add AI to their applications. We welcome feedback, questions, comments, and contributions. Feel free to contact us at [contact@assistant-engine.com](mailto:contact@assistant-engine.com).


## PRO Version

For users looking for enhanced functionality, the **PRO Version** offers advanced features beyond the standard Filament Assistant capabilities:
- **Tool Call Confirmations:** Require explicit user confirmation for specific tool calls before they are executed, ensuring an extra layer of safety.
- **RAG:** Index documents and make them accessible via a dedicated RAG tool.
- **Conversational Memory Management:** Enhance your assistants with dynamic conversational memory, featuring an intuitive admin panel that lets users view and manage stored memories for improved context awareness and more natural interactions.
- **Assistant Admin Panel:** Easily configure your assistants and tools.
- **Monitoring and Analytics:** Benefit from built-in monitoring and analytic capabilities to keep track of performance and usage.

If you are interested in the **PRO Version** or would like to learn more about its implementation, please contact us at [contact@assistant-engine.com](mailto:contact@assistant-engine.com) for further details and access options.

## Consultancy & Support

Do you need assistance integrating Filament Assistant into your Laravel Filament application, or help setting it up?  
We offer consultancy services to help you get the most out of our package, whether you’re just getting started or looking to optimize an existing setup.

Reach out to us at [contact@assistant-engine.com](mailto:contact@assistant-engine.com).

## Contributing

We welcome contributions from the community! Feel free to submit pull requests, open issues, and help us improve the package.

## License

This project is licensed under the MIT License. Please see [License File](LICENSE.md) for more information.
