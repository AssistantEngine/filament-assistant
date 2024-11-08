<?php

namespace AssistantEngine\Filament\Components;

use AssistantEngine\Filament\Services\ConversationService;
use AssistantEngine\SDK\AssistantEngine;
use AssistantEngine\SDK\Models\Conversation\Conversation;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Facades\Filament;
use Filament\FilamentManager;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantSidebar  extends Component
{
    const EVENT_ASSISTANT_SIDEBAR_OPEN = 'filament-assistant:sidebar:open';
    const EVENT_ASSISTANT_SIDEBAR_CLOSE = 'filament-assistant:sidebar:close';

    public $showWithoutTrigger = false;
    public $threadId = null;

    public $visible = false;

    public $width = 400;

    protected AssistantEngine $assistantEngine;
    protected ConversationService $conversationService;


    /**
     * @param AssistantEngine $assistantEngine
     * @return void
     */
    public function boot(AssistantEngine $assistantEngine, ConversationService $conversationService)
    {
        $this->assistantEngine = $assistantEngine;
        $this->conversationService = $conversationService;

        if ($this->showWithoutTrigger && $conversationService->hasConversationOption()) {
            $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_OPEN, $conversationService->getActiveConversationOption());
        }
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_OPEN)]
    public function openSidebar($conversationOption = []): void
    {
        $option = new ConversationOption($conversationOption['assistant_key'], $conversationOption);

        $conversation = $this->assistantEngine->findOrCreateConversation($option);

        $this->visible = true;
        $this->initConversation($conversation);
    }

    public function closeSidebar()
    {
        $this->visible = false;
        $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_CLOSE);
    }

    public function render()
    {
        return view('filament-assistant::assistant-sidebar');
    }

    private function initConversation(Conversation $thread)
    {
        $this->threadId = $thread->id;
    }
}
