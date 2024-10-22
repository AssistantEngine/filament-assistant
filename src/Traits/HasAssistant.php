<?php

namespace AssistantEngine\Filament\Traits;

use AssistantEngine\Filament\Components\AssistantButton;
use AssistantEngine\Filament\Components\AssistantSidebar;
use AssistantEngine\Filament\Factories\AssistantActionFactory;
use AssistantEngine\Laravel\Components\ChatComponent;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Livewire\Attributes\On;

trait HasAssistant
{
    public bool $insideAssistant = false;
    public string $pageDescriptionContextKey = 'pageDescription';

    public function booted()
    {
        $this->listeners[ChatComponent::EVENT_RUN_FINISHED] = '$refresh';
        $option = self::getResource()::assistant($this);

        $action = AssistantActionFactory::getGlobalAssistantAction($option);

        $this->cacheAction($action);
    }

    /**
     * Will be used if the component inside the chat modal changes
     * @return mixed
     */
    public function getAssistantContext()
    {
        /** @var ConversationOption $option */
        $option = self::getResource()::assistant($this);

        return $option->context;
    }

    /**
     * Retrieves the assistant subject key associated with this instance.
     *
     * This method returns a string key that uniquely identifies the subject
     * within the assistant context. By default, it returns `null`, but you can
     * override it in subclasses to provide a specific subject key.
     *
     * @return string|null The assistant subject key, or `null` if not set.
     */
    public function getAssistantSubjectKey(): ?string
    {
        return null;
    }

    /**
     * Will add a page description to the conversation context.
     *
     * @return string|null
     */
    public function getAssistantPageDescription(): ?string
    {
        return null;
    }

    /**
     * Adds additional models to the assistant context for processing.
     *
     * Each model must use the trait `AssistantContext`.
     *
     * The input array should be structured as follows:
     *
     * ```php
     * [
     *     'ClassName' => [$models],
     *     // ...
     * ]
     * ```
     *
     * Where:
     * - `'ClassName'` is the fully qualified class name of the model.
     * - `[$models]` is an array of model instances of that class.
     *
     * @param array $additionalContextModels An associative array of models to add to the context.
     * @return array The processed array of context models.
     */
    public function getAdditionalAssistantContextModels()
    {
        return [];
    }

    #[On(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_REQUEST_PAGE_ASSISTANT)]
    public function triggerAssistantSidebar(): void
    {
        $option = self::getResource()::assistant($this);

        $this->dispatch(AssistantSidebar::EVENT_ASSISTANT_SIDEBAR_LOAD_CONVERSATION, $option);
    }

    #[On(AssistantButton::EVENT_OPEN_ASSISTANT)]
    public function openAssistant($trigger): void
    {
        if ($trigger === AssistantButton::ASSISTANT_BUTTON_TRIGGER_SIDEBAR) {
            $this->triggerAssistantSidebar();
        } else {
            $this->mountAction("open_assistant_action");
        }
    }

    #[On(AssistantButton::EVENT_CLOSE_ASSISTANT)]
    public function closeAssistant(): void
    {
        $this->unmountAction();
    }
}
