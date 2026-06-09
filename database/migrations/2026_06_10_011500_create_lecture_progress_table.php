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
        Schema::create('lecture_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lecture_id')->constrained('lectures')->onDelete('cascade');
            $table->unsignedInteger('seconds_watched')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('last_watched_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'lecture_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecture_progress');
    }
};
