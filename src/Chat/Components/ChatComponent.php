<?php

namespace AssistantEngine\Filament\Chat\Components;

use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Driver\DefaultChatDriver;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatComponent extends Component
{
    const EVENT_CONVERSATION_RESET = 'filament-assistant:reset-conversation';
    const EVENT_PROCESS_MESSAGE = 'filament-assistant:process-message';
    const EVENT_SHOULD_SCROLL = 'filament-assistant:should-scroll';
    const EVENT_RUN_FINISHED = 'filament-assistant:run-finished';

    protected ?ChatDriverInterface $driver = null;

    /**
     * ## Internal States ###
     */
    public $showScrollIcon = false;
    public $changedMessageIndexes = [];

    /**
     * ## Conversation States ###
     */
    public $conversationId;
    public $isRunning = false;
    public $messages = []; // Plain array of messages for Livewire

    public $assistantName;
    public $assistantDescription;

    public function boot(ChatDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Initialize the component on mount.
     *
     * @param string $conversationId
     */
    public function mount(string $conversationId): void
    {
        $this->conversationId = $conversationId;

        $this->initializeComponent();
    }

    /**
     * Process an incoming message and then update the component state.
     *
     * @param string $message
     */
    #[On(self::EVENT_PROCESS_MESSAGE)]
    public function processMessage($message): void
    {
        $this->driver->sendMessage($this->conversationId, $message);

        // Instead of calling reloadMessages(), we update state directly.
        $this->initializeComponent();
        $this->dispatch(self::EVENT_SHOULD_SCROLL);
    }

    #[On(self::EVENT_CONVERSATION_RESET)]
    public function resetConversation()
    {
        $conversation = $this->driver->recreate($this->conversationId);
        $this->conversationId = $conversation->identifier;
        $this->showScrollIcon = false;

        $this->initializeComponent();
    }

    /**
     * Reloads the conversation state.
     *
     * This method can be triggered separately if needed.
     */
    public function loadMessages(): void
    {
        $wasRunning = $this->isRunning;
        $previousMessageCount = count($this->messages);

        $this->initializeComponent();

        if ($previousMessageCount !== count($this->messages)) {
            if ($this->showScrollIcon === false) {
                // This means the user didnt scroll up after sending the message
                $this->dispatch(self::EVENT_SHOULD_SCROLL);
            }

            for ($i = $previousMessageCount; $i < count($this->messages); $i++) {
                if (!in_array($i, $this->changedMessageIndexes)) {
                    $this->changedMessageIndexes[] = $i;
                }
            }
        }

        if ($wasRunning === true && $this->isRunning === false) {
            $updatedMessages = [];

            foreach ($this->changedMessageIndexes as $index) {
                if (isset($this->messages[$index])) {
                    $updatedMessages[] = $this->messages[$index];
                }
            }

            $this->dispatch(self::EVENT_RUN_FINISHED, $updatedMessages);

            // Reset the index list.
            $this->changedMessageIndexes = [];
        }
    }

    public function scrollDown()
    {
        $this->dispatch(self::EVENT_SHOULD_SCROLL);
    }

    /**
     * Centralized method to initialize the component state from the conversation model.
     *
     * This method fetches the conversation, then sets the conversation ID,
     * the last-run state, and maps the conversation's messages.
     *
     * @return void
     */
    private function initializeComponent(): void
    {
        $conversation = $this->driver->findConversationByID($this->conversationId);

        // Update component state from the conversation model.
        $this->conversationId = $conversation->identifier;
        $this->isRunning = $conversation->isRunning;
        //$this->isRunning = false;
        $this->messages = array_map(function ($message) {
            return method_exists($message, 'toArray') ? $message->toArray() : $message;
        }, $conversation->messages);

        // Set the assistant info from the conversation model.
        $this->assistantName = $conversation->assistantName;
        $this->assistantDescription = $conversation->assistantDescription;
    }

    /**
     * Render the chat component view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('filament-assistant::livewire.chat-component');
    }
}
