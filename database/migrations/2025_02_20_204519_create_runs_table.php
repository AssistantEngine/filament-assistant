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
        Schema::create('fa_runs', function (Blueprint $table) {
            $table->id();
            // A run belongs to a thread.
            $table->foreignId('thread_id')->constrained('fa_threads')->onDelete('cascade');
            // The assistant key associated with the run.
            $table->string('assistant_key');
            // Status enum with the specified states.
            $table->enum('status', [
                'queued',
                'expired',
                'in_progress',
                'tool_calling',
                'cancelling',
                'cancelled',
                'completed',
                'incompleted',
                'failed'
            ]);
            // JSON columns for run settings and additional run data.
            $table->json('run_settings')->nullable();
            $table->json('additional_run_data')->nullable();
            $table->json('additional_tools')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fa_runs');
    }
};
