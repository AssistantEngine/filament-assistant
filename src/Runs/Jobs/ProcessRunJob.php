<?php
namespace AssistantEngine\Filament\Runs\Jobs;

use AssistantEngine\Filament\Runs\Contracts\RunProcessorInterface;
use AssistantEngine\Filament\Runs\Models\Run;
use AssistantEngine\Filament\Runs\Services\RunProcessorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Run $run;

    /**
     * @param int $runId The ID of the run to process.
     */
    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    /**
     * Retrieve the run, and process it via the RunProcessorService.
     *
     * @param RunProcessorService $processor
     * @return void
     * @throws \Exception if the run is not found.
     */
    public function handle(RunProcessorInterface $processor): void
    {
        $processor->process($this->run);
    }
}
