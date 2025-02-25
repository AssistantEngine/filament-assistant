<?php

namespace AssistantEngine\Filament\Chat\Contracts;

use AssistantEngine\Filament\Chat\Models\ConversationOption;
use AssistantEngine\Filament\Chat\Models\ConversationViewModel;

/**
 * Interface ChatDriverInterface
 *
 * Defines the methods required for a chat driver.
 */
interface ChatDriverInterface
{
    public function findOrCreateConversation(ConversationOption $option): ConversationViewModel;
    public function findConversationByID(string $conversationId): ConversationViewModel;
    public function recreate(string $conversationId): ConversationViewModel;
    public function sendMessage(string $conversationId, string $message): ConversationViewModel;
}
