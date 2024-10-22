<?php

namespace AssistantEngine\Filament\Components;

use AssistantEngine\SDK\AssistantEngine;
use AssistantEngine\SDK\Models\Conversation\Conversation;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Facades\Filament;
use Filament\FilamentManager;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantSidebar  extends Component
{
    const EVENT_ASSISTANT_SIDEBAR_OPEN = 'filament-assistant:sidebar:open';
    const EVENT_ASSISTANT_SIDEBAR_CLOSE = 'filament-assistant:sidebar:close';
    const EVENT_ASSISTANT_SIDEBAR_LOAD_CONVERSATION = 'filament-assistant:sidebar:load-conversation';
    const EVENT_ASSISTANT_SIDEBAR_REQUEST_PAGE_ASSISTANT = 'filament-assistant:sidebar:request-page-assistant';

    public $showWithoutTrigger = false;
    public $threadId = null;

    protected AssistantEngine $assistantEngine;

    /**
     * @param AssistantEngine $assistantEngine
     * @return void
     */
    public function boot(AssistantEngine $assistantEngine)
    {
        $this->assistantEngine = $assistantEngine;

        if ($this->showWithoutTrigger && AssistantButton::isGlobalAssistantAvailable()) {
            $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_OPEN);
            $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_REQUEST_PAGE_ASSISTANT);
        }
    }

    private function initConversation(Conversation $thread)
    {
        $this->threadId = $thread->id;
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_LOAD_CONVERSATION)]
    public function processConversationOption($option): void
    {

        $option = new ConversationOption($option['assistant_key'], $option);
        $conversation = $this->assistantEngine->findOrCreateConversation($option);

        $this->initConversation($conversation);

        $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_OPEN);
    }

    public function closeSidebar()
    {
        $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_CLOSE);
    }

    public function render()
    {
        return view('filament-assistant::assistant-sidebar');
    }
}
