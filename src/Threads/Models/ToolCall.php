<?php

namespace AssistantEngine\Filament\Threads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ToolCall
 *
 * Represents a tool call.
 *
 * @property int $id
 * @property string $call_id
 * @property string $call_function
 * @property array|null $call_arguments
 * @property string|null $response_content
 * @property string $status
 *
 * @property-read Message $message
 */
class ToolCall extends Model
{
    protected $table = 'fa_tool_calls';

    protected $fillable = [
        'call_id',
        'call_function',
        'call_arguments',
        'response_content',
        'status',
    ];

    protected $casts = [
        'call_arguments' => 'array',
        'response_content' => 'array',
    ];

    // Enum constants for the status field.
    public const STATUS_IN_PROGRESS          = 'in_progress';
    public const STATUS_REQUIRES_CONFIRMATION = 'requires_confirmation';
    public const STATUS_SUCCESS               = 'success';
    public const STATUS_CANCELED              = 'canceled';
    public const STATUS_ERROR                 = 'error';
    public const STATUS_EXPIRED               = 'expired';


    /**
     * The message that this tool call is associated with.
     *
     * @return BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
