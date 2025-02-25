<?php

namespace AssistantEngine\Filament\Chat\Models;

/**
 * Class ConversationOption
 *
 * Options for finding or creating a conversation.
 */
class ConversationOption
{
    public string $assistantKey;
    public string $userId;
    public array $metadata;
    public bool $recreate;
    public array $additionalRunData;
    public array $additionalTools;

    public function __construct(
        string $assistantKey,
        string $userId,
        array $metadata = [],
        bool $recreate = false,
        array $additionalRunData = [],
        array $additionalTools = []
    ) {
        $this->assistantKey = $assistantKey;
        $this->userId = $userId;
        $this->metadata = $metadata;
        $this->recreate = $recreate;
        $this->additionalRunData = $additionalRunData;
        $this->additionalTools = $additionalTools;
    }
}
