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
        Schema::table('modules', function (Blueprint $table) {
            // Check if course_id constraint exists and drop it (SQLite handles drops cleanly in Laravel 11)
            try {
                $table->dropForeign(['course_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('course_id');
            
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        });

        Schema::table('live_classes', function (Blueprint $table) {
            try {
                $table->dropForeign(['course_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('course_id');
            
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            try {
                $table->dropForeign(['subject_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('subject_id');
            
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        });

        Schema::table('live_classes', function (Blueprint $table) {
            try {
                $table->dropForeign(['subject_id']);
            } catch (\Exception $e) {}
            $table->dropColumn('subject_id');
            
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        });
    }
};
