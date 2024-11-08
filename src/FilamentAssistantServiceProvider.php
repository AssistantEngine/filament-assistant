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
use Spatie\LaravelPackageTools\Commands\InstallCommand;
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
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->askToStarRepoOnGitHub('assistant-engine/filament-assistant');
            })
            ->hasViews('filament-assistant');
    }

    public function bootingPackage()
    {
        Livewire::component('filament-assistant::global-button', AssistantButton::class);
        Livewire::component('filament-assistant::assistant-sidebar', AssistantSidebar::class);
    }
}
