<?php

namespace AssistantEngine\Filament;

use AssistantEngine\Filament\Chat\Components\AssistantButton;
use AssistantEngine\Filament\Chat\Components\ChatComponent;
use AssistantEngine\Filament\Chat\Components\ChatInputComponent;
use AssistantEngine\Filament\Chat\Components\Sidebar;
use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Contracts\ContextResolverInterface;
use AssistantEngine\Filament\Chat\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\Filament\Chat\Driver\DefaultChatDriver;
use AssistantEngine\Filament\Chat\Resolvers\ContextResolver;
use AssistantEngine\Filament\Chat\Resolvers\ConversationOptionResolver;
use AssistantEngine\Filament\Runs\Contracts\RunProcessorInterface;
use AssistantEngine\Filament\Runs\Services\RunProcessorService;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAssistantServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-assistant')
            ->hasConfigFile()
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function registeringPackage()
    {
        $this->app->bind(ChatDriverInterface::class, function ($app) {
            $chatDriverClass = config(
                'filament-assistant.chat_driver',
                DefaultChatDriver::class
            );

            if (!class_exists($chatDriverClass)) {
                throw new \Exception("Chat driver class {$chatDriverClass} does not exist.");
            }

            $chatDriver = app($chatDriverClass);

            if (!($chatDriver instanceof ChatDriverInterface)) {
                throw new \Exception("Chat driver {$chatDriverClass} must implement ChatDriverInterface.");
            }

            return $chatDriver;
        });

        $this->app->bind(RunProcessorInterface::class, function ($app) {
            $runProcessorClass = config(
                'filament-assistant.run_processor',
                RunProcessorService::class
            );

            if (!class_exists($runProcessorClass)) {
                throw new \Exception("Run Processor class {$runProcessorClass} does not exist.");
            }

            $runProcessor = app($runProcessorClass);

            if (!($runProcessor instanceof RunProcessorInterface)) {
                throw new \Exception("Run processor {$runProcessorClass} must implement RunProcessorInterface.");
            }

            return $runProcessor;
        });

        $this->app->bind(ContextResolverInterface::class, function ($app) {
            $globalContextResolverClass = Config::get(
                'filament-assistant.context_resolver',
                ContextResolver::class
            );


            if (!class_exists($globalContextResolverClass)) {
                throw new \Exception("Context resolver class {$globalContextResolverClass} does not exist.");
            }

            $contextResolver = app($globalContextResolverClass);

            if (!($contextResolver instanceof ContextResolverInterface)) {
                throw new \Exception("Context Resolver {$globalContextResolverClass} must implement ContextResolverInterface.");
            }

            return $contextResolver;
        });

        $this->app->bind(ConversationOptionResolverInterface::class, function ($app) {
            $globalConversationResolverClass = Config::get(
                'filament-assistant.conversation_resolver',
                ConversationOptionResolver::class
            );


            if (!class_exists($globalConversationResolverClass)) {
                throw new \Exception("Conversation resolver class {$globalConversationResolverClass} does not exist.");
            }

            $conversationResolver = app($globalConversationResolverClass);

            if (!($conversationResolver instanceof ConversationOptionResolverInterface)) {
                throw new \Exception("Conversation Resolver {$globalConversationResolverClass} must implement ConversationOptionResolverInterface.");
            }

            return $conversationResolver;
        });
    }

    public function bootingPackage()
    {
        Livewire::component('filament-assistant::chat-component', ChatComponent::class);
        Livewire::component('filament-assistant::chat-input-component', ChatInputComponent::class);
        Livewire::component('filament-assistant::sidebar', Sidebar::class);
        Livewire::component('filament-assistant::assistant-button', AssistantButton::class);
    }
}
