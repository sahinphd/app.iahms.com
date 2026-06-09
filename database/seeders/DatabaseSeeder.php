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
        // 1. Create School Classes
        $classA = \App\Models\SchoolClass::create([
            'name' => 'Class A: Radiography Basics',
            'description' => 'First-year medical and radiography students'
        ]);

        $classB = \App\Models\SchoolClass::create([
            'name' => 'Class B: Advanced Scanning',
            'description' => 'Second-year specialization course'
        ]);

        // 2. Create Default Users
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
            'school_class_id' => $classA->id,
            'is_approved' => true,
        ]);

        $sain = User::create([
            'name' => 'Sain',
            'email' => 'sain@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'teacher',
            'school_class_id' => $classA->id,
            'is_approved' => true,
        ]);

        $student = User::create([
            'name' => 'John Student Doe',
            'email' => 'student@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'student',
            'school_class_id' => $classA->id, // Assign to Class A
            'is_approved' => true,
        ]);

        // 3. Create a default Course with duration
        $course = \App\Models\Course::create([
            'title' => 'Introduction to Clinical Radiography',
            'description' => 'A foundational course detailing radiographic positioning, radiation safety, and basic anatomical imaging techniques for paramedical students.',
            'thumbnail' => null,
            'teacher_id' => $teacher->id,
            'school_class_id' => $classA->id, // Course belongs to Class A
            'is_published' => true,
            'duration' => '8 Weeks',
        ]);

        // 4. Create a Subject
        $subject = \App\Models\Subject::create([
            'course_id' => $course->id,
            'title' => 'Radiography Basics',
            'description' => 'First-semester core subject introducing basic radiographic concepts.',
            'duration' => '4 Weeks',
        ]);

        // 5. Create a Module under the Subject
        $module = \App\Models\Module::create([
            'subject_id' => $subject->id,
            'title' => 'Module 1: Radiographic Positioning Principles',
        ]);

        // 6. Create a Video Lecture with duration
        \App\Models\Lecture::create([
            'module_id' => $module->id,
            'title' => '1.1 Basics of Chest X-Ray Positioning',
            'file_path' => 'videos/' . $course->id . '/chest_positioning_v1.mp4',
            'duration' => '12 mins',
        ]);

        // 7. Create a Study Material
        \App\Models\Material::create([
            'module_id' => $module->id,
            'title' => 'Chest Radiography Guideline PDF',
            'file_path' => 'materials/' . $course->id . '/chest_radiography_guide.pdf',
        ]);

        // 8. Seed Default Settings
        \App\Models\Setting::set('site_logo_text', 'IAHMS LMS');
        \App\Models\Setting::set('site_logo_subtext', 'SECURE LEARNING');
        \App\Models\Setting::set('theme_primary_color', '#8b5cf6');
        \App\Models\Setting::set('theme_bg_color', '#0f172a');
        \App\Models\Setting::set('theme_sidebar_color', '#020617');

        // 9. Assign Teachers dynamically (join tables)
        // Dr. Robert Smith assignments
        $course->teachers()->attach($teacher->id, ['role' => 'course_admin']);
        $classA->teachers()->attach($teacher->id, ['role' => 'class_admin']);
        $subject->teachers()->attach($teacher->id, ['role' => 'subject_teacher']);

        // Sain assignments
        $subject->teachers()->attach($sain->id, ['role' => 'subject_teacher']);

        // 7. Seed Default Permission Toggles
        $permissions = [
            'manage_users',
            'manage_role_permissions',
            'manage_classes',
            'create_courses',
            'edit_courses',
            'delete_courses',
            'publish_courses',
            'manage_syllabus',
            'manage_live_classes',
            'manage_enrollments',
            'view_reports',
            'manage_teacher_profiles',
            'manage_student_profiles'
        ];

        // Seed teacher permissions (most allowed, except reports and admin-only)
        $allowedTeacherPerms = [
            'create_courses',
            'edit_courses',
            'delete_courses',
            'publish_courses',
            'manage_syllabus',
            'manage_live_classes',
            'manage_enrollments'
        ];

        foreach ($permissions as $perm) {
            \App\Models\RolePermission::create([
                'role' => 'teacher',
                'permission' => $perm,
                'is_allowed' => in_array($perm, $allowedTeacherPerms)
            ]);

            \App\Models\RolePermission::create([
                'role' => 'student',
                'permission' => $perm,
                'is_allowed' => false
            ]);
        }
    }
}
