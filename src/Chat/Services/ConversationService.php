<?php

namespace AssistantEngine\Filament\Chat\Services;

use AssistantEngine\Filament\Chat\Contracts\ContextResolverInterface;
use AssistantEngine\Filament\Chat\Contracts\ConversationOptionResolverInterface;
use AssistantEngine\Filament\Chat\Models\ConversationOption;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;
use Livewire\Mechanisms\HandleComponents\HandleComponents;

class ConversationService
{
    /**
     * @var ConversationOption|null Stores the active conversation option instance.
     */
    private ?ConversationOption $conversationOption = null;
    private ConversationOptionResolverInterface $conversationOptionResolver;
    private ContextResolverInterface $contextResolver;

    public function __construct(ConversationOptionResolverInterface $conversationOptionResolver, ContextResolverInterface $contextResolver)
    {
        $this->conversationOptionResolver = $conversationOptionResolver;
        $this->contextResolver = $contextResolver;
    }

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

        $this->conversationOption = $this->conversationOptionResolver->resolve($page);

        if ($this->conversationOption) {
            $context = $this->contextResolver->resolve($page);

            if ($context) {
                $this->conversationOption->additionalRunData = array_merge_recursive($this->conversationOption->additionalRunData, $context);
            }
        }

        return $this->conversationOption;
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
