<?php

namespace AssistantEngine\Filament\Runs\Services;

use AssistantEngine\Filament\Runs\Jobs\ProcessRunJob;
use AssistantEngine\Filament\Runs\Models\Run;
use AssistantEngine\Filament\Threads\Models\Thread;
use AssistantEngine\Filament\Assistants\Models\Assistant;
use AssistantEngine\Filament\Threads\Repositories\MessageRepository;
use Illuminate\Support\Facades\Config;

class RunDispatcherService
{
    protected MessageRepository $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    /**
     * Create a new run with status "queued" and dispatch the processing job.
     *
     * @param Thread $thread
     * @param Assistant $assistant
     * @return void
     */
    public function run(Thread $thread, Assistant $assistant): void
    {
        $queueConfig = Config::get('filament-assistant.default_run_queue', 'default');

        // Create a new run record with a queued status.
        $run = Run::create([
            'thread_id'           => $thread->id,
            'assistant_key'       => $assistant->key,
            'status'              => Run::STATUS_QUEUED,
            'additional_run_data' => $thread->additional_run_data,
            'additional_tools'    => $thread->additional_tools,
            'run_settings'        => [
                'llm_url' => $assistant->llmConnection->url,
                'model'   => $assistant->model,
            ],
        ]);

        // Dispatch the job with only the run ID.
        ProcessRunJob::dispatch($run)->onQueue($queueConfig);
    }
}
