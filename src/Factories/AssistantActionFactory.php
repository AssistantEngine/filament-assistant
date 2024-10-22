<?php

namespace AssistantEngine\Filament\Factories;

use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;

class AssistantActionFactory
{
    public static function getGlobalAssistantAction(ConversationOption $option, $label = 'Assistant'): \Filament\Actions\Action
    {
        return
            \Filament\Actions\Action::make('open_assistant_action')
                ->action(function ( \Filament\Actions\Action $action, array $data): void {
                    $action->halt();
                })
                ->modalHeading(config('assistant-engine.filament.assistant-modal.heading', ""))
                ->modalWidth(config('assistant-engine.filament.assistant-modal.max-width', MaxWidth::Full))
                ->slideOver(config('assistant-engine.filament.assistant-modal.slide-over', false))
                ->modalContent(function () use ($option) {
                    return view('filament-assistant::assistant-modal-container', [
                        'option' => $option,
                        'maxVisibleTabs' => config('assistant-engine.filament.assistant-modal.max-visible-tabs', 7),
                        'showTabs' => config('assistant-engine.filament.assistant-modal.show-tabs', false),
                        'showComponent' => config('assistant-engine.filament.assistant-modal.show-page-component', false),
                        'autoHeight' => config('assistant-engine.filament.assistant-modal.auto-height', false),
                        'slideOver' => config('assistant-engine.filament.assistant-modal.slide-over', false),
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(false);
    }
}
