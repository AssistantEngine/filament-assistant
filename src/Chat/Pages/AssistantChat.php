<?php

namespace AssistantEngine\Filament\Chat\Pages;

use AssistantEngine\Filament\Assistants\Repositories\AssistantRepository;
use AssistantEngine\Filament\Chat\Contracts\ChatDriverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
use AssistantEngine\Filament\Chat\Models\ConversationViewModel;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;

class AssistantChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';
    protected static string $view = 'filament-assistant::pages.assistant-chat';

    /**
     * Optionally store the active assistant key.
     *
     * If no query parameter is provided, you can fallback to a default assistant
     * as specified in your configuration.
     */
    public string $activeAssistant;

    /**
     * The active conversation view model.
     */
    public $threadId;

    public function mount(): void
    {
        // Get the active assistant key from the query parameter,
        // or use a default value from config if none is provided.
        $this->activeAssistant = request()->query('assistant', config('filament-assistant.default_assistant'));

        // Build the conversation option and retrieve the active conversation
        $this->threadId = $this->getActiveConversation()->identifier;
    }

    /**
     * Build subnavigation items for each assistant.
     *
     * Each navigation item:
     * - Uses the assistant's name as its label.
     * - Appends the assistant key to the current URL as a query parameter.
     * - Checks if it matches the current active assistant to set its active state.
     *
     * @return NavigationItem[]
     */
    public function getSubNavigation(): array
    {
        /** @var AssistantRepository $assistantRepository */
        $assistantRepository = app(AssistantRepository::class);
        $assistants = $assistantRepository->getAllAssistants();

        $subNavItems = [];

        foreach ($assistants as $assistantKey => $assistant) {
            // Build a URL with the assistant query parameter.
            $url = request()->fullUrlWithQuery(['assistant' => $assistantKey]);

            // Determine if this assistant is active.
            $isActive = request()->query('assistant', config('filament-assistant.default_assistant')) === $assistantKey;

            $subNavItems[] = NavigationItem::make($assistant->name)
                ->url($url)
                ->isActiveWhen(function () use ($isActive) {
                    return $isActive;
                });
        }

        return $subNavItems;
    }

    /**
     * Build a conversation option for the active assistant and retrieve
     * the corresponding conversation from the chat driver.
     *
     * This is similar to the logic found in your Conversation Option Resolver,
     * but uses the active assistant key from the query parameter.
     *
     * @return ConversationViewModel|null
     */
    public function getActiveConversation(): ?ConversationViewModel
    {
        if (!auth()->check()) {
            return null;
        }

        // Create a conversation option using the active assistant key and the current user.
        $conversationOption = new ConversationOption($this->activeAssistant, auth()->user()->id);

        // Retrieve (or create) the conversation using the chat driver.
        /** @var ChatDriverInterface $chatDriver */
        $chatDriver = app(ChatDriverInterface::class);
        return $chatDriver->findOrCreateConversation($conversationOption);
    }
}