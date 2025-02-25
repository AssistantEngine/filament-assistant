<?php

namespace AssistantEngine\Filament\Chat\Components;

use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Services\ConversationService;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantButton  extends Component
{
    public $visible = true;

    public $options = [];


    public function boot(ConversationService $conversationService)
    {
        if (!$conversationService->hasConversationOption()) {
            $this->visible = false;
        }
    }

    public function openAssistant()
    {
        $this->dispatch(Sidebar::EVENT_ASSISTANT_SIDEBAR_OPEN);

        $this->visible = false;
    }

    #[On(Sidebar::EVENT_ASSISTANT_SIDEBAR_CLOSE)]
    public function handleSidebarClose(): void
    {
        $this->visible = true;
    }

    public function render()
    {
        return view('filament-assistant::livewire.assistant-button');
    }
}
