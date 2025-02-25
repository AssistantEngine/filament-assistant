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
        Schema::create('fa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('fa_threads')->onDelete('cascade');
            // Enum values: assistant, user
            $table->enum('role', ['assistant', 'user']);
            // Make content nullable now.
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fa_messages');
    }
};
