<?php

namespace AssistantEngine\Filament\Chat\Components;

use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
use AssistantEngine\Filament\Chat\Services\ConversationService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\HandleComponents;

class Sidebar extends Component
{
    const EVENT_ASSISTANT_SIDEBAR_OPEN = 'filament-assistant:sidebar:open';
    const EVENT_ASSISTANT_SIDEBAR_CLOSE = 'filament-assistant:sidebar:close';

    public $threadId = null;

    public $visible = false;
    public $openByDefault;
    public $width;


    /**
     * @return void
     */
    public function boot(ConversationService $conversationService, ChatDriverInterface $driver)
    {
        if ($conversationService->hasConversationOption()) {
            $conversation = $driver->findOrCreateConversation($conversationService->getActiveConversationOption());

            $this->threadId = $conversation->identifier;

            if ($this->openByDefault) {
                $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_OPEN);
            }
        }
    }

    #[On(Sidebar::EVENT_ASSISTANT_SIDEBAR_OPEN)]
    public function openSidebar($threadId = null): void
    {
        if ($threadId) {
            $this->threadId = $threadId;
        }

        $this->visible = true;
    }

    public function closeSidebar()
    {
        $this->visible = false;

        $this->dispatch(self::EVENT_ASSISTANT_SIDEBAR_CLOSE);
    }

    public function render()
    {
        return view('filament-assistant::livewire.sidebar');
    }
}
