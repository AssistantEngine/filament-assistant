<?php

namespace AssistantEngine\Filament\Threads\Repositories;

use AssistantEngine\Filament\Threads\Models\Message;
use AssistantEngine\Filament\Threads\Models\ToolCall as EloquentToolCall;

class MessageRepository
{
    /**
     * Adds a new message for the given thread.
     */
    public function addMessage($thread, string $role, ?string $content = null, ?int $toolCallId = null): Message
    {
        return $thread->messages()->create([
            'role'    => $role,
            'content' => $content,
            'tool_call_id' => $toolCallId
        ]);
    }
}
