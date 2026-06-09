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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false);
            $table->string('duration')->nullable();
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->string('duration')->nullable();
        });

        Schema::table('live_classes', function (Blueprint $table) {
            $table->integer('duration_minutes')->default(60);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['is_completed', 'duration']);
        });

        Schema::table('lectures', function (Blueprint $table) {
            $table->dropColumn('duration');
        });

        Schema::table('live_classes', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
