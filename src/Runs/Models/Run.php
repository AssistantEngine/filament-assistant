<?php

namespace AssistantEngine\Filament\Runs\Models;

use AssistantEngine\Filament\Threads\Models\Thread;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Run
 *
 * Represents a run within a conversation thread.
 *
 * @property int $id
 * @property int $thread_id
 * @property string $assistant_key
 * @property string $status
 * @property array|null $run_settings
 * @property array|null $additional_run_data
 * @property array|null $additional_tools
 *
 * @property-read Thread $thread
 * @property-read \Illuminate\Database\Eloquent\Collection|RunStep[] $runSteps
 */
class Run extends Model
{
    protected $table = 'fa_runs';

    protected $fillable = [
        'thread_id',
        'assistant_key',
        'status',
        'run_settings',
        'additional_run_data',
        'additional_tools',
    ];

    protected $casts = [
        'run_settings'         => 'array',
        'additional_run_data'  => 'array',
        'additional_tools'     => 'array',
    ];

    // Enum constants for the status field.
    public const STATUS_QUEUED       = 'queued';
    public const STATUS_EXPIRED      = 'expired';
    public const STATUS_IN_PROGRESS  = 'in_progress';
    public const STATUS_TOOL_CALLING = 'tool_calling';
    public const STATUS_CANCELLING   = 'cancelling';
    public const STATUS_CANCELLED    = 'cancelled';
    public const STATUS_COMPLETED    = 'completed';
    public const STATUS_INCOMPLETED  = 'incompleted';
    public const STATUS_FAILED       = 'failed';

    /**
     * Get the thread that owns the run.
     *
     * @return BelongsTo
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Get the run steps for this run.
     *
     * @return HasMany
     */
    public function runSteps(): HasMany
    {
        return $this->hasMany(RunStep::class);
    }

    /**
     * Determine if the run is currently running.
     *
     * A run is considered running if its status is one of:
     * queued, in_progress, tool_calling, or cancelling.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return in_array($this->status, [
            self::STATUS_QUEUED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_TOOL_CALLING,
            self::STATUS_CANCELLING,
        ]);
    }
}
