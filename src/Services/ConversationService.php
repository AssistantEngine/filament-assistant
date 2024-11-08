<?php

namespace AssistantEngine\Filament\Services;

use AssistantEngine\Filament\Contracts\ContextResolverInterface;
use AssistantEngine\Filament\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\SDK\Models\Options\ConversationOption;
use Filament\Pages\Page;
use Livewire\Mechanisms\HandleComponents\HandleComponents;

class ConversationService
{
    /**
     * @var ConversationOption|null Stores the active conversation option instance.
     */
    private ?ConversationOption $conversationOption = null;

    /**
     * Checks if there is an active conversation option available.
     *
     * @return bool True if an active conversation option exists, false otherwise.
     */
    public function hasConversationOption(): bool
    {
        return $this->getActiveConversationOption() !== null;
    }

    /**
     * Retrieves the active conversation option, initializing it if necessary.
     *
     * @return ConversationOption|null The active conversation option or null if none is available.
     */
    public function getActiveConversationOption(): ?ConversationOption
    {
        if ($this->conversationOption) {
            return $this->conversationOption;
        }

        $page = self::getActivePage();

        if (!$page) {
            return null;
        }

        $this->conversationOption = $this->getGlobalConversationOption($page);

        if ($this->conversationOption) {
            $context = $this->getActiveContext($page);

            if ($context) {
                $this->conversationOption->context = array_merge_recursive($this->conversationOption->context, $context);
            }
        }

        return $this->conversationOption;
    }

    /**
     * Resolves the active context for the provided page.
     *
     * @param Page $page The current Filament page.
     * @return array The resolved context array.
     * @throws \Exception If the configured context resolver does not implement ContextResolverInterface.
     */
    private function getActiveContext(Page $page): array
    {
        $globalContextResolverClass = config("assistant-engine.filament-assistant.conversation-option.context-resolver");

        if (!$globalContextResolverClass) {
            return [];
        }

        /** @var ContextResolverInterface $resolver */
        $resolver = new $globalContextResolverClass();

        if (!$resolver instanceof ContextResolverInterface) {
            throw new \Exception("ContextResolver must implement ContextResolverInterface");
        }

        return $resolver->resolve($page);
    }

    /**
     * Retrieves the global conversation option for the provided page.
     *
     * @param Page $page The current Filament page.
     * @return ConversationOption|null The resolved conversation option or null if not available.
     * @throws \Exception If the configured conversation resolver does not implement ConversationOptionResolverInterface.
     */
    private function getGlobalConversationOption(Page $page): ?ConversationOption
    {
        $globalResolverClass = config("assistant-engine.filament-assistant.conversation-option.conversation-resolver");

        if (!$globalResolverClass) {
            throw new \Exception("No ConversationResolver defined");
        }

        /** @var ConversationOptionResolverInterface $resolver */
        $resolver = new $globalResolverClass();

        if (!$resolver instanceof ConversationOptionResolverInterface) {
            throw new \Exception("ConversationResolver must implement ConversationOptionResolverInterface");
        }

        return $resolver->resolve($page);
    }

    /**
     * Retrieves the active Filament page from the Livewire component stack.
     *
     * @return Page|null The active page if available, otherwise null.
     */
    public static function getActivePage(): ?Page
    {
        /** @var HandleComponents $handleComponents */
        $handleComponents = app(HandleComponents::class);

        foreach ($handleComponents::$componentStack as $component) {
            if ($component instanceof Page) {
                return $component;
            }
        }

        return null;
    }
}
