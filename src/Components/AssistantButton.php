<?php

namespace AssistantEngine\Filament\Components;

use AssistantEngine\Filament\Services\ConversationService;
use Filament\Support\Assets\Css;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantButton extends Component
{
    public $visible = true;

    public $options = [];

    public array $conversationOption = [];

    private ConversationService $conversationService;

    public function boot(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;

        if ($conversationService->hasConversationOption()) {
            $this->visible = true;
            $this->conversationOption = $conversationService->getActiveConversationOption()->toArray();
        } else {
            $this->visible = false;
        }
    }

    public function openAssistant()
    {
        $this->dispatch(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_OPEN, $this->conversationOption);

        $this->visible = false;
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_CLOSE)]
    public function handleSidebarClose(): void
    {
        $this->visible = true;
    }

    public function render()
    {
        return view('filament-assistant::assistant-button');
    }
}
