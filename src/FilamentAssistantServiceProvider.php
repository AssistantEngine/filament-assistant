<?php

namespace AssistantEngine\Filament;

use AssistantEngine\Filament\Commands\PublishConfigCommand;
use AssistantEngine\Filament\Components\AssistantButton;
use AssistantEngine\Filament\Components\AssistantSidebar;
use AssistantEngine\Filament\Components\AssistantModal;
use AssistantEngine\Laravel\Commands\LaravelAssistantChatCommand;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
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
            ->hasCommand(PublishConfigCommand::class)
            ->hasConfigFile("filament-assistant")
            ->hasViews('filament-assistant');
    }

    public function bootingPackage()
    {
        Livewire::component('filament-assistant::global-button', AssistantButton::class);
        Livewire::component('filament-assistant::assistant-modal', AssistantModal::class);
        Livewire::component('filament-assistant::assistant-sidebar', AssistantSidebar::class);


        $showSidebarWithoutTrigger = false;
        if (config('assistant-engine.filament.assistant-sidebar.render')) {
            $showSidebarWithoutTrigger = config('assistant-engine.filament.assistant-sidebar.show-without-trigger', false);
            FilamentView::registerRenderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                function () {
                    return Blade::render('<div id="filament-assistant::main-container">');
                },
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::FOOTER,
                function () use ($showSidebarWithoutTrigger) {
                    return Blade::render(
                        '</div><div id="filament-assistant::chat-sidebar" class="hidden border-l bg-white fixed right-0 bottom-0 top-0 overflow-y-scroll">
                                    <livewire:filament-assistant::assistant-sidebar :showWithoutTrigger="$showWithoutTrigger"/>
                               </div>',
                        [
                            'showWithoutTrigger' => $showSidebarWithoutTrigger,
                        ]);
                },
            );
        }

        if (config('assistant-engine.filament.assistant-button.show')) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::BODY_END,
                function () use ($showSidebarWithoutTrigger) {
                    return Blade::render('<livewire:filament-assistant::global-button :visible="$isVisible" :trigger="$trigger" :options="$options" />', [
                        'isVisible' => (bool) $showSidebarWithoutTrigger === false,
                        'trigger' => config('assistant-engine.filament.assistant-button.trigger'),
                        'options' => config('assistant-engine.filament.assistant-button.options')
                    ], true);
                }
            );
        }
    }
}
