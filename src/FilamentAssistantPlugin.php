<?php

namespace AssistantEngine\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class FilamentAssistantPlugin implements Plugin
{

    public function getId(): string
    {
        return 'filament-assistant::plugin';
    }

    public function register(Panel $panel): void
    {
        // TODO: Implement register() method.
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function boot(Panel $panel): void
    {
        $showSidebarWithoutTrigger = false;
        if (config('assistant-engine.filament-assistant.sidebar.render')) {
            $showSidebarWithoutTrigger = config('assistant-engine.filament-assistant.sidebar.show-without-trigger', false);
            $width = config('assistant-engine.filament-assistant.sidebar.width', 400);

            if (is_int($width) === false) {
                throw new \Exception('assistant sidebar width must be an integer');
            }

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
                function () use ($showSidebarWithoutTrigger, $width) {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render(
                        '<livewire:filament-assistant::assistant-sidebar :width="$width" :showWithoutTrigger="$showWithoutTrigger"/>',
                        [
                            'showWithoutTrigger' => $showSidebarWithoutTrigger,
                            'width' => $width,
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
        }

        if (config('assistant-engine.filament-assistant.button.show') && config('assistant-engine.filament-assistant.sidebar.render')) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::PAGE_END,
                function () use ($showSidebarWithoutTrigger) {
                    if (!auth()->check()) {
                        return Blade::render("");
                    }

                    return Blade::render('<livewire:filament-assistant::global-button :visible="$isVisible"  :options="$options" />', [
                        'isVisible' => (bool) $showSidebarWithoutTrigger === false,
                        'options' => config('assistant-engine.filament-assistant.button.options')
                    ], true);
                }
            );
        }
    }
}
