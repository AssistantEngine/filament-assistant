<?php

namespace AssistantEngine\Filament;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use AssistantEngine\FilamentAssistant\Commands\FilamentAssistantCommand;

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
            ->hasViews();
    }
}
