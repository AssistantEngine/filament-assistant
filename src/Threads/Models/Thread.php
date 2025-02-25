<?php

namespace AssistantEngine\Filament\Threads\Models;

use AssistantEngine\Filament\Runs\Models\Run;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Thread
 *
 * Represents a conversation thread.
 *
 * @property int $id
 * @property string $assistant_key
 * @property array|null $metadata
 * @property string $user_identifier
 * @property array|null $additional_run_data
 * @property array|null $additional_tools
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Message[] $messages
 * @property-read \Illuminate\Database\Eloquent\Collection|Run[] $runs
 */
class Thread extends Model
{
    protected $table = 'fa_threads';

    protected $fillable = [
        'assistant_key',
        'metadata',
        'user_identifier',
        'additional_run_data',
        'additional_tools',
    ];

    protected $casts = [
        'metadata'             => 'array',
        'additional_run_data'  => 'array',
        'additional_tools'     => 'array',
    ];

    /**
     * A thread may have multiple messages.
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * A thread may have multiple runs.
     *
     * @return HasMany
     */
    public function runs(): HasMany
    {
        return $this->hasMany(Run::class);
    }

    /**
     * Retrieve the most recent run associated with this thread.
     *
     * @return Run|null
     */
    public function getLastRun(): ?Run
    {
        return $this->runs()->orderBy('id', 'desc')->first();
    }

    /**
     * Determine if the thread is currently running based on its last run.
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        $lastRun = $this->getLastRun();
        return $lastRun && $lastRun->isRunning();
    }
}
