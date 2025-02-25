<?php

namespace AssistantEngine\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;

class FilamentAssistantPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-assistant::plugin';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        // TODO: Implement register() method.
    }

    public function boot(Panel $panel): void
    {
        $sidebar = Config::get('filament-assistant.sidebar', []);
        $button = Config::get('filament-assistant.button', []);

        if ($sidebar['enabled']) {

            FilamentView::registerRenderHook(
                PanelsRenderHook::TOPBAR_BEFORE,
                function () {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render('<div id="filament-assistant::topbar-container" class="sticky top-0 z-20 overflow-x-clip">');
                },
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::TOPBAR_AFTER,
                function () {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render('</div>');
                },
            );


            FilamentView::registerRenderHook(
                PanelsRenderHook::CONTENT_START,
                function () {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render('<div id="filament-assistant::main-container">');
                },
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::PAGE_END,
                function () use ($sidebar) {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render(
                        '<livewire:filament-assistant::sidebar :width="$width" :openByDefault="$openByDefault"/>',
                        [
                            'openByDefault' => $sidebar['open_by_default'],
                            'width' => $sidebar['width'],
                        ]);
                },
            );

            FilamentView::registerRenderHook(
                PanelsRenderHook::CONTENT_END,
                function () {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render('</div>');
                },
            );

            if ($button['show']) {
                FilamentView::registerRenderHook(
                    PanelsRenderHook::PAGE_END,
                    function () use ($sidebar, $button) {
                        if (!auth()->check()) {
                            return Blade::render("");
                        }

                        return Blade::render('<livewire:filament-assistant::assistant-button :visible="$isVisible"  :options="$options" />', [
                            'isVisible' => (bool) $sidebar['open_by_default'] === false,
                            'options' => $button['options']
                        ], true);
                    }
                );
            }
        }
    }
}
