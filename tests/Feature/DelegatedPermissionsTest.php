<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RolePermission;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DelegatedPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database using DatabaseSeeder
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_admin_can_toggle_role_permissions()
    {
        $admin = User::where('role', 'admin')->first();

        // Toggle 'create_courses' to false for teachers
        $response = $this->actingAs($admin)
            ->post(route('admin.permissions.toggle'), [
                'role' => 'teacher',
                'permission' => 'create_courses',
                'is_allowed' => 0
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('role_permissions', [
            'role' => 'teacher',
            'permission' => 'create_courses',
            'is_allowed' => false
        ]);
    }

    public function test_admin_can_toggle_user_permission_override()
    {
        $admin = User::where('role', 'admin')->first();
        $teacher = User::where('email', 'teacher@lms.com')->first();

        // Force allow 'manage_student_profiles' override for teacher
        $response = $this->actingAs($admin)
            ->post(route('admin.permissions.user.toggle', $teacher->id), [
                'permission' => 'manage_student_profiles',
                'value' => 'allow'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_permissions', [
            'user_id' => $teacher->id,
            'permission' => 'manage_student_profiles',
            'is_allowed' => true
        ]);

        // Inherit (delete override)
        $responseInherit = $this->actingAs($admin)
            ->post(route('admin.permissions.user.toggle', $teacher->id), [
                'permission' => 'manage_student_profiles',
                'value' => 'inherit'
            ]);

        $responseInherit->assertStatus(200);
        $this->assertDatabaseMissing('user_permissions', [
            'user_id' => $teacher->id,
            'permission' => 'manage_student_profiles'
        ]);
    }

    public function test_unauthorized_user_cannot_access_user_directory()
    {
        $teacher = User::where('email', 'teacher@lms.com')->first();

        // By default, teacher cannot manage student or teacher profiles
        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function test_delegated_student_manager_can_only_manage_students()
    {
        $teacher = User::where('email', 'teacher@lms.com')->first();
        $admin = User::where('role', 'admin')->first();

        // 1. Admin allows teacher to manage student profiles
        UserPermission::create([
            'user_id' => $teacher->id,
            'permission' => 'manage_student_profiles',
            'is_allowed' => true
        ]);

        // 2. Teacher accesses directory
        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        
        // Should see the student user
        $student = User::where('role', 'student')->first();
        $response->assertSee($student->name);

        // Should NOT see any teacher or admin users
        $response->assertDontSee($admin->name);

        // 3. Try to toggle approval for student (allowed)
        $responseToggleStudent = $this->actingAs($teacher)
            ->post(route('admin.users.toggle-approval', $student->id));
        
        $responseToggleStudent->assertRedirect();
        
        // Try to toggle approval for teacher (forbidden)
        $sain = User::where('email', 'sain@lms.com')->first();
        $responseToggleTeacher = $this->actingAs($teacher)
            ->post(route('admin.users.toggle-approval', $sain->id));
        
        $responseToggleTeacher->assertStatus(403);
    }

    public function test_delegated_teacher_manager_can_only_manage_teachers()
    {
        $teacher = User::where('email', 'teacher@lms.com')->first();
        $admin = User::where('role', 'admin')->first();
        $sain = User::where('email', 'sain@lms.com')->first();
        $student = User::where('role', 'student')->first();

        // 1. Admin allows teacher to manage teacher profiles
        UserPermission::create([
            'user_id' => $teacher->id,
            'permission' => 'manage_teacher_profiles',
            'is_allowed' => true
        ]);

        // 2. Teacher accesses directory
        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);

        // Should see another teacher user
        $response->assertSee($sain->name);

        // Should NOT see student
        $response->assertDontSee($student->name);

        // 3. Try to toggle approval for teacher (allowed)
        $responseToggleTeacher = $this->actingAs($teacher)
            ->post(route('admin.users.toggle-approval', $sain->id));
        
        $responseToggleTeacher->assertRedirect();

        // Try to toggle approval for student (forbidden)
        $responseToggleStudent = $this->actingAs($teacher)
            ->post(route('admin.users.toggle-approval', $student->id));
        
        $responseToggleStudent->assertStatus(403);
    }
}
