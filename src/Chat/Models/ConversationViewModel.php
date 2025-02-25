<?php

namespace AssistantEngine\Filament\Chat\Models;

class ConversationViewModel
{
    public string $identifier;
    public bool $isRunning;
    /** @var array List of messages (UserMessage, AssistantMessage, etc.) */
    public array $messages;
    public ?string $assistantName;
    public ?string $assistantDescription;

    public function __construct(
        string $identifier,
        bool $isRunning = true,
        array $messages = [],
        ?string $assistantName = null,
        ?string $assistantDescription = null
    ) {
        $this->identifier = $identifier;
        $this->isRunning = $isRunning;
        $this->messages = $messages;
        $this->assistantName = $assistantName;
        $this->assistantDescription = $assistantDescription;
    }
}
