<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lecture;
use App\Models\LiveClass;
use App\Models\ClassNote;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressAndAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database using DatabaseSeeder
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_student_can_save_lecture_progress_and_complete()
    {
        $student = User::where('role', 'student')->first();
        $lecture = Lecture::first();

        // 1. Student must be enrolled in the course parent class to be enrolled in the course or assigned to it
        $course = $lecture->module->subject->course;
        
        // Ensure student has approved enrollment in course
        \App\Models\Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'is_approved' => true
        ]);

        // POST progress update
        $response = $this->actingAs($student)
            ->post(route('lectures.progress', $lecture->id), [
                'seconds_watched' => 120,
                'is_completed' => false
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('lecture_progress', [
            'user_id' => $student->id,
            'lecture_id' => $lecture->id,
            'seconds_watched' => 120,
            'is_completed' => false
        ]);

        // Complete the lecture
        $responseComplete = $this->actingAs($student)
            ->post(route('lectures.progress', $lecture->id), [
                'seconds_watched' => 300,
                'is_completed' => true
            ]);

        $responseComplete->assertStatus(200);
        
        $this->assertDatabaseHas('lecture_progress', [
            'user_id' => $student->id,
            'lecture_id' => $lecture->id,
            'is_completed' => true
        ]);

        // Assert UsageLog registered the completion
        $this->assertDatabaseHas('usage_logs', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'completed_lecture'
        ]);
    }

    public function test_student_joining_live_class_logs_attendance()
    {
        $student = User::where('role', 'student')->first();
        
        $course = Course::first();
        $subject = Subject::first();

        // Create a live class
        $liveClass = LiveClass::create([
            'subject_id' => $subject->id,
            'title' => 'Interactive Radiology Session',
            'datetime' => now()->addHours(2),
            'link' => 'https://meet.google.com/abc-defg-hij'
        ]);

        // Ensure student has approved enrollment
        \App\Models\Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'is_approved' => true
        ]);

        // GET join route
        $response = $this->actingAs($student)
            ->get(route('live-classes.join', $liveClass->id));

        // Should redirect to Meet URL
        $response->assertRedirect('https://meet.google.com/abc-defg-hij');

        // Check attendance record exists
        $this->assertDatabaseHas('live_class_attendance', [
            'user_id' => $student->id,
            'live_class_id' => $liveClass->id
        ]);

        // Check UsageLog registered the join
        $this->assertDatabaseHas('usage_logs', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'action' => 'join_live_class'
        ]);
    }

    public function test_global_bulletin_storing_null_class_displays_on_dashboard()
    {
        $teacher = User::where('role', 'teacher')->first();
        
        // POST a notice with class ID = "all"
        $response = $this->actingAs($teacher)
            ->post(route('teacher.class-notes.store'), [
                'school_class_id' => 'all',
                'title' => 'Global Announcement Title',
                'content' => 'This is a global notice for all classes.'
            ]);

        $response->assertRedirect();
        
        // Assert stored with null school_class_id
        $this->assertDatabaseHas('class_notes', [
            'teacher_id' => $teacher->id,
            'school_class_id' => null,
            'title' => 'Global Announcement Title',
            'content' => 'This is a global notice for all classes.'
        ]);

        // Access student dashboard to make sure it appears
        $student = User::where('role', 'student')->first();
        $dashboardResponse = $this->actingAs($student)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Global Announcement Title');
    }

    public function test_user_can_update_profile_settings()
    {
        $student = User::where('role', 'student')->first();

        $response = $this->actingAs($student)
            ->post(route('profile.update'), [
                'name' => 'Updated Student Name',
                'email' => 'updated_student@lms.com',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123'
            ]);

        $response->assertRedirect();
        
        $student->refresh();
        $this->assertEquals('Updated Student Name', $student->name);
        $this->assertEquals('updated_student@lms.com', $student->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $student->password));
    }
}
