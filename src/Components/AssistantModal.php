<?php

namespace AssistantEngine\Filament\Components;

use AssistantEngine\Filament\Traits\HasAssistant;
use AssistantEngine\Laravel\Components\ChatComponent;
use AssistantEngine\SDK\AssistantEngine;
use AssistantEngine\SDK\Models\Conversation\Conversation;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use AssistantEngine\SDK\Models\Options\ConversationUpdateOption;
use Livewire\Attributes\On;
use Livewire\Component;

class AssistantModal extends Component
{
    const EVENT_ASSISTANT_MODAL_MOUNTED = 'filament-assistant:modal:mounted';
    const EVENT_ASSISTANT_MODAL_REMOVED = 'filament-assistant:modal:removed-tab';
    const EVENT_ASSISTANT_MODAL_LINK = 'filament-assistant:modal:redirect-page';
    const EVENT_ASSISTANT_MODAL_CHANGED = 'filament-assistant:modal:tab-changed';

    public $tabs = [];
    public $threadId;
    public $pageClass;
    public $recordId;
    public $maxHeight = 0;
    public $showMoreTabs = false;
    public $maxVisibleTabs = 2;
    public $showTabs = false;
    public $showComponent = false;
    public $autoHeight = false;
    public $slideOver = false;

    protected AssistantEngine $assistantEngine;

    public function boot(AssistantEngine $assistantEngine)
    {
        $this->assistantEngine = $assistantEngine;
    }

    /**
     * @param ConversationOption $option
     * @param AssistantEngine $assistantEngine
     * @return void
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function mount(ConversationOption $option, bool $showTabs = false, bool $showComponent = false, bool $autoHeight = false): void
    {
        $this->showTabs = $showTabs;
        $this->showComponent = $showComponent;
        $this->autoHeight = $autoHeight;

        $conversation = $this->assistantEngine->findOrCreateConversation($option);
        $activeConversations = $this->assistantEngine->getConversations($option->user_id);

        $this->initConversation($conversation);
        $this->initActiveTabs($activeConversations);
        $this->prependActiveTab($conversation->id);

        $this->dispatch(self::EVENT_ASSISTANT_MODAL_MOUNTED);
    }

    private function initConversation(Conversation $thread)
    {
        $this->threadId = $thread->id;
        $this->pageClass = $thread->additional_data['page_class'] ?? null;
        $this->recordId = $thread->additional_data['record_id'] ?? null;
    }

    private function initActiveTabs($activeThreads)
    {
        foreach ($activeThreads as $conversation) {
            /** @var Conversation $conversation */
            $this->tabs[$conversation->id] = [
                'id' => $conversation->id,
                'name' => $conversation->title ?? $conversation->id,
                'thread_user_id' => $conversation->id
            ];
        }
    }

    public function prependActiveTab($key)
    {
        if (isset($this->tabs[$key])) {
            $activeTab = $this->tabs[$key];
            unset($this->tabs[$key]);
            $this->tabs = [$key => $activeTab] + $this->tabs; // Prepend to the array
        }
    }

    public function setActiveTab($threadId, $prepend = false)
    {
        $conversation = $this->assistantEngine->getConversation($threadId);
        $this->initConversation($conversation);

        if ($prepend) {
            $this->toggleMoreTabs();
            $this->prependActiveTab($threadId);
        }

        $this->dispatch(ChatComponent::EVENT_CHANGE_CONVERSATION, $conversation->toArray());
    }

    public function toggleMoreTabs()
    {
        $this->showMoreTabs = !$this->showMoreTabs;
    }

    #[On(ChatComponent::EVENT_CONVERSATION_RESET)]
    public function resetConversation($conversationData)
    {
        $conversation = new Conversation($conversationData);

        $this->tabs = $this->replaceArrayKeys($this->tabs, [$this->threadId => $conversation->id]);
        $this->initConversation($conversation);

        $this->tabs[$conversation->id] = [
            'id' => $conversation->id,
            'name' => $conversation->title ?? $conversation->id,
            'thread_user_id' => $conversation->id
        ];
    }

    private function replaceArrayKeys(array $array, array $keyMapping): array
    {
        $updatedArray = [];

        foreach ($array as $key => $value) {
            $newKey = $keyMapping[$key] ?? $key;
            $updatedArray[$newKey] = $value;
        }

        return $updatedArray;
    }

    private function getPageComponentContext($page, $record): array
    {
        /** @var HasAssistant $component */
        $component = new $page();
        $component->insideAssistant = true;
        $component->mount($record);

        return $component->getAssistantContext();
    }


    public function removeTab($threadId)
    {
        $this->assistantEngine->deactivateConversation($threadId);

        unset($this->tabs[$threadId]);

        if (empty($this->tabs)) {
            $this->threadId = null;
            $this->pageClass = null;
            $this->recordId = null;

            $this->dispatch(AssistantButton::EVENT_CLOSE_ASSISTANT);
        } else {
            if ($this->threadId == $threadId) {
                $this->threadId = count($this->tabs) > 0 ? array_key_first($this->tabs) : null;
                $this->setActiveTab($this->threadId);
            }
        }

        $this->dispatch(self::EVENT_ASSISTANT_MODAL_REMOVED);
    }

    #[On(AssistantModal::EVENT_ASSISTANT_MODAL_LINK)]
    public function redirectPage($recordId, $page)
    {
        $update = new ConversationUpdateOption();
        $update->additional_data = [
            'page_class' => $page,
            'record_id' => $recordId,
        ];
        $update->context = $this->getPageComponentContext($page, $recordId);

        $thread = $this->assistantEngine->updateConversation($this->threadId, $update);
        $this->initConversation($thread);

        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('filament-assistant::assistant-modal');
    }
}
