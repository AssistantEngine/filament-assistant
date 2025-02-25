<?php

namespace AssistantEngine\Filament\Runs\Models;

use AssistantEngine\Filament\Threads\Models\Message;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class RunStep
 *
 * Represents a step within a run.
 *
 * @property int $id
 * @property int $run_id
 * @property string $type
 * @property array|null $raw_response
 * @property string $status
 * @property array|null $function_definitions
 * @property array|null $message_history
 *
 * @property-read Run $run
 * @property-read \Illuminate\Database\Eloquent\Collection|Message[] $messages
 */
class RunStep extends Model
{
    protected $table = 'fa_run_steps';

    protected $fillable = [
        'run_id',
        'type',
        'raw_response',
        'status',
        'function_definitions',
        'message_history',
    ];

    protected $casts = [
        'raw_response'         => 'array',
        'function_definitions' => 'array',
        'message_history'      => 'array',
    ];

    // Enum constants for the type field.
    public const TYPE_MESSAGE_CREATION = 'message_creation';
    public const TYPE_TOOL_CALLS       = 'tool_calls';

    // Enum constants for the status field.
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_FAILED      = 'failed';
    public const STATUS_CANCELLED   = 'cancelled';
    public const STATUS_EXPIRED     = 'expired';

    /**
     * Get the run that owns this run step.
     *
     * @return BelongsTo
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the messages associated with this run step.
     *
     * Assumes that a nullable foreign key `run_step_id` exists in the messages table.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'run_step_id');
    }
}
