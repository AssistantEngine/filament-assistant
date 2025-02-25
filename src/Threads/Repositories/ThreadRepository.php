<?php

namespace AssistantEngine\Filament\Threads\Repositories;

use AssistantEngine\Filament\Threads\Models\Thread;
use AssistantEngine\Filament\Chat\Models\ConversationOption;

class ThreadRepository
{
    /**
     * Finds an existing thread matching the assistant key and user identifier,
     * or creates a new one if none exists (or if a recreation is requested).
     */
    public function findOrCreate(ConversationOption $option): Thread
    {
        /** @var Thread $thread */
        $thread = Thread::where('assistant_key', $option->assistantKey)
            ->where('user_identifier', $option->userId)
            ->orderBy('id', 'desc')
            ->first();

        if (!$thread || $option->recreate) {
            $thread = Thread::create([
                'assistant_key'       => $option->assistantKey,
                'user_identifier'     => $option->userId,
                'metadata'            => $option->metadata,
                'additional_run_data' => $option->additionalRunData,
                'additional_tools'    => $option->additionalTools, // store additional tool identifiers
            ]);
        } else {
            $thread->metadata = $option->metadata;
            $thread->additional_run_data = $option->additionalRunData;
            $thread->additional_tools = $option->additionalTools; // update additional tools if provided
            $thread->save();
        }

        return $thread;
    }

    /**
     * Retrieves a thread by its ID.
     */
    public function findById(string $id): ?Thread
    {
        return Thread::find($id);
    }

    public function createFromThread(Thread $thread): Thread
    {
        return Thread::create([
            'assistant_key'       => $thread->assistant_key,
            'user_identifier'     => $thread->user_identifier,
            'metadata'            => $thread->metadata,
            'additional_run_data' => $thread->additional_run_data,
            'additional_tools'    => $thread->additional_tools,
        ]);
    }
}
