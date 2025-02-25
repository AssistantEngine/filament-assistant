<?php

namespace AssistantEngine\Filament\Threads\Models;

use AssistantEngine\Filament\Runs\Models\RunStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Message
 *
 * Represents a message in a thread.
 *
 * @property int $id
 * @property int $thread_id
 * @property int|null $tool_call_id
 * @property int|null $run_step_id
 * @property string $role
 * @property string|null $content
 *
 * @property-read Thread $thread
 * @property-read ToolCall|null $toolCall
 * @property-read RunStep|null $runStep
 */
class Message extends Model
{
    protected $table = 'fa_messages';

    protected $fillable = [
        'thread_id',
        'role',
        'content',
        'tool_call_id',
        'run_step_id', // new field to relate a message to a run step.
    ];

    // Enum constants for the role field.
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_USER = 'user';

    /**
     * The thread that this message belongs to.
     *
     * @return BelongsTo
     */
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * The tool call associated with this message.
     *
     * @return BelongsTo
     */
    public function toolCall(): BelongsTo
    {
        return $this->belongsTo(ToolCall::class);
    }

    /**
     * The run step that this message belongs to.
     *
     * @return BelongsTo
     */
    public function runStep(): BelongsTo
    {
        return $this->belongsTo(RunStep::class, 'run_step_id');
    }
}
