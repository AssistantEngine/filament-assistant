<?php
namespace AssistantEngine\Filament\Chat\Components;

use Livewire\Attributes\Reactive;
use Livewire\Component;

class ChatInputComponent extends Component
{
    public $messageInput = '';
    // New property to control whether the input is disabled.

    #[Reactive]
    public $disabled = false;

    /**
     * When the user clicks the send button, emit the message to the parent.
     */
    public function sendMessage()
    {
        if (trim($this->messageInput) === '' || $this->disabled) {
            return;
        }

        $this->dispatch(ChatComponent::EVENT_PROCESS_MESSAGE, $this->messageInput);
        $this->messageInput = '';
    }

    /**
     * When the trash button is clicked, emit an event to reset the conversation.
     */
    public function resetConversation()
    {
        $this->dispatch(ChatComponent::EVENT_CONVERSATION_RESET);
    }

    public function render()
    {
        return view('filament-assistant::livewire.chat-input-component');
    }
}
