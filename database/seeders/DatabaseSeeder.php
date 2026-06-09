<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Default Users
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $teacher = User::create([
            'name' => 'Dr. Robert Smith',
            'email' => 'teacher@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'teacher',
            'is_approved' => true,
        ]);

        $student = User::create([
            'name' => 'John Student Doe',
            'email' => 'student@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'student',
            'is_approved' => true,
        ]);

        // 2. Create a default Course
        $course = \App\Models\Course::create([
            'title' => 'Introduction to Clinical Radiography',
            'description' => 'A foundational course detailing radiographic positioning, radiation safety, and basic anatomical imaging techniques for paramedical students.',
            'thumbnail' => null,
            'teacher_id' => $teacher->id,
            'is_published' => true,
        ]);

        // 3. Create a Module
        $module = \App\Models\Module::create([
            'course_id' => $course->id,
            'title' => 'Module 1: Radiographic Positioning Principles',
        ]);

        // 4. Create a Video Lecture
        \App\Models\Lecture::create([
            'module_id' => $module->id,
            'title' => '1.1 Basics of Chest X-Ray Positioning',
            'file_path' => 'videos/' . $course->id . '/chest_positioning_v1.mp4',
        ]);

        // 5. Create a Study Material
        \App\Models\Material::create([
            'module_id' => $module->id,
            'title' => 'Chest Radiography Guideline PDF',
            'file_path' => 'materials/' . $course->id . '/chest_radiography_guide.pdf',
        ]);
    }
}
