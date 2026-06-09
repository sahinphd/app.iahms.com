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
        // 1. Course Assignment mapping
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('teacher'); // e.g. 'course_admin', 'teacher'
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
        });

        // 2. School Class Assignment mapping
        Schema::create('school_class_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('teacher'); // e.g. 'class_admin', 'teacher'
            $table->timestamps();

            $table->unique(['school_class_id', 'user_id']);
        });

        // 3. Subject Assignment mapping
        Schema::create('subject_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('teacher'); // e.g. 'subject_teacher', 'teacher'
            $table->timestamps();

            $table->unique(['subject_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_user');
        Schema::dropIfExists('school_class_user');
        Schema::dropIfExists('course_user');
    }
};
