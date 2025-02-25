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
        Schema::create('fa_threads', function (Blueprint $table) {
            $table->id();
            $table->string('assistant_key');
            $table->string('user_identifier');
            $table->json('metadata')->nullable();
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
        Schema::dropIfExists('threads');
    }
};
