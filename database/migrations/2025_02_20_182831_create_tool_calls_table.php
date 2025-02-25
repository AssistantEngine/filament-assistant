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
        Schema::create('fa_tool_calls', function (Blueprint $table) {
            $table->id();
            // Enum values: in_progress, requires_confirmation, success, canceled, error, expired
            $table->enum('status', [
                'in_progress',
                'requires_confirmation',
                'success',
                'canceled',
                'error',
                'expired'
            ]);
            $table->string('call_id');
            $table->string('call_function');
            $table->json('call_arguments')->nullable();
            $table->json('response_content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_calls');
    }
};
