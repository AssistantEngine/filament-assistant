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
        Schema::table('fa_messages', function (Blueprint $table) {
            $table->foreignId('run_step_id')
                ->after('thread_id')
                ->nullable()
                ->constrained('fa_run_steps')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fa_messages', function (Blueprint $table) {
            $table->dropForeign(['run_step_id']);
            $table->dropColumn('run_step_id');
        });
    }
};
