<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fa_run_steps', function (Blueprint $table) {
            $table->id();
            // Each runstep belongs to a run.
            $table->foreignId('run_id')->constrained('fa_runs')->onDelete('cascade');
            // Enum for the type with the specified values.
            $table->enum('type', [
                'message_creation',
                'tool_calls'
            ]);
            // Status enum for runstep.
            $table->enum('status', [
                'in_progress',
                'completed',
                'failed',
                'cancelled',
                'expired'
            ]);
            // JSON columns for function definitions and message history.
            $table->json('function_definitions')->nullable();
            $table->json('message_history')->nullable();
            // Raw response in JSON.
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fa_run_steps');
    }
};
