<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Module;
use App\Models\RolePermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LectureUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database using DatabaseSeeder
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_admin_can_upload_lecture_video()
    {
        Storage::fake('public');

        $admin = User::where('role', 'admin')->first();
        $module = Module::first();
        
        $response = $this->actingAs($admin)
            ->post(route('lectures.store'), [
                'module_id' => $module->id,
                'title' => 'Test X-Ray Positioning Video',
                'duration' => '15 mins',
                'video' => UploadedFile::fake()->create('positioning.mp4', 500, 'video/mp4')
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Assert file exists in mocked storage
        $lecture = \App\Models\Lecture::where('title', 'Test X-Ray Positioning Video')->first();
        $this->assertNotNull($lecture);
        Storage::disk('public')->assertExists($lecture->file_path);
    }

    public function test_assigned_teacher_can_upload_lecture_video()
    {
        Storage::fake('public');

        $teacher = User::where('email', 'teacher@lms.com')->first();
        $module = Module::first();

        $response = $this->actingAs($teacher)
            ->post(route('lectures.store'), [
                'module_id' => $module->id,
                'title' => 'Teacher X-Ray Positioning Video',
                'duration' => '10 mins',
                'video' => UploadedFile::fake()->create('positioning.mp4', 500, 'video/mp4')
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $lecture = \App\Models\Lecture::where('title', 'Teacher X-Ray Positioning Video')->first();
        $this->assertNotNull($lecture);
        Storage::disk('public')->assertExists($lecture->file_path);
    }

    public function test_unassigned_teacher_cannot_upload_lecture_video()
    {
        Storage::fake('public');

        $unassignedTeacher = User::create([
            'name' => 'Dr. Unassigned',
            'email' => 'unassigned@lms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'teacher',
            'is_approved' => true,
        ]);

        $module = Module::first();

        $response = $this->actingAs($unassignedTeacher)
            ->post(route('lectures.store'), [
                'module_id' => $module->id,
                'title' => 'Hack Chest X-Ray Video',
                'duration' => '5 mins',
                'video' => UploadedFile::fake()->create('positioning.mp4', 500, 'video/mp4')
            ]);

        $response->assertStatus(403);
    }
}
