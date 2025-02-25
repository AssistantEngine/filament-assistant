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
            $table->foreignId('tool_call_id')
                ->after('content')
                ->nullable()
                ->constrained('fa_tool_calls')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fa_messages', function (Blueprint $table) {
            $table->dropForeign(['tool_call_id']);
            $table->dropColumn('tool_call_id');
        });
    }
};
