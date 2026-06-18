<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\SchoolClass;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccessAndApprovalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed standard roles and default permissions/settings
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * Student allotted to a class gets their account auto-approved.
     */
    public function test_student_allotted_to_class_gets_auto_approved()
    {
        $admin = User::where('role', 'admin')->first();
        
        // Create an unapproved student
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => false,
            'school_class_id' => null,
        ]);

        $class = SchoolClass::first();

        // Perform assignment
        $response = $this->actingAs($admin)
            ->post(route('admin.classes.assign-student'), [
                'student_id' => $student->id,
                'school_class_id' => $class->id,
            ]);

        $response->assertRedirect();
        
        // Student should now be approved and have the class ID set
        $student->refresh();
        $this->assertEquals($class->id, $student->school_class_id);
        $this->assertTrue((bool)$student->is_approved);
    }

    /**
     * Students bulk allotted to a class get their accounts auto-approved.
     */
    public function test_students_bulk_allotted_to_class_get_auto_approved()
    {
        $admin = User::where('role', 'admin')->first();
        
        // Create unapproved students
        $student1 = User::factory()->create([
            'role' => 'student',
            'is_approved' => false,
            'school_class_id' => null,
        ]);
        $student2 = User::factory()->create([
            'role' => 'student',
            'is_approved' => false,
            'school_class_id' => null,
        ]);

        $class = SchoolClass::first();

        // Perform bulk allotment
        $response = $this->actingAs($admin)
            ->post(route('admin.classes.allot-students-bulk'), [
                'school_class_id' => $class->id,
                'students' => [$student1->id, $student2->id],
            ]);

        $response->assertRedirect();

        // Both students should be approved and have the class ID set
        $student1->refresh();
        $student2->refresh();

        $this->assertEquals($class->id, $student1->school_class_id);
        $this->assertTrue((bool)$student1->is_approved);

        $this->assertEquals($class->id, $student2->school_class_id);
        $this->assertTrue((bool)$student2->is_approved);
    }

    /**
     * Student enrollment approval auto-approves the student user account.
     */
    public function test_student_enrollment_approval_auto_approves_student_account()
    {
        $admin = User::where('role', 'admin')->first();
        
        // Create an unapproved student
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => false,
            'school_class_id' => null,
        ]);

        $course = Course::first();

        // Create pending enrollment
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'is_approved' => false,
        ]);

        // Approve enrollment
        $response = $this->actingAs($admin)
            ->post(route('enrollments.approve', $enrollment->id));

        $response->assertRedirect();

        $enrollment->refresh();
        $student->refresh();

        $this->assertTrue((bool)$enrollment->is_approved);
        $this->assertTrue((bool)$student->is_approved);
    }

    /**
     * Student can view courses of the allotted class.
     */
    public function test_student_can_view_courses_of_allotted_class()
    {
        // Set up student in Class B
        $classB = SchoolClass::where('name', 'Class B: Advanced Scanning')->first();
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
            'school_class_id' => $classB->id,
        ]);

        // Create course assigned to Class B
        $teacher = User::where('role', 'teacher')->first();
        $course = Course::create([
            'title' => 'Advanced Scanning Masterclass',
            'description' => 'Advanced techniques',
            'teacher_id' => $teacher->id,
            'school_class_id' => $classB->id,
            'is_published' => true,
        ]);

        // Request course catalog
        $response = $this->actingAs($student)
            ->get(route('courses.index'));

        $response->assertStatus(200);
        $response->assertSee('Advanced Scanning Masterclass');
    }

    /**
     * Student can view courses they are enrolled in, even if they belong to a different class.
     */
    public function test_student_can_view_courses_they_are_enrolled_in_even_if_different_class()
    {
        // Class A and Course A (Introduction to Clinical Radiography) already exist from seeder.
        // Course A is assigned to Class A.
        $courseA = Course::where('title', 'Introduction to Clinical Radiography')->first();
        
        // Student belongs to Class B (Advanced Scanning)
        $classB = SchoolClass::where('name', 'Class B: Advanced Scanning')->first();
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
            'school_class_id' => $classB->id,
        ]);

        // Create approved enrollment for student in Course A
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $courseA->id,
            'is_approved' => true,
        ]);

        // Act as student, verify they can see Course A
        $response = $this->actingAs($student)
            ->get(route('courses.index'));

        $response->assertStatus(200);
        $response->assertSee('Introduction to Clinical Radiography');
    }

    /**
     * Admin dashboard shows pending course enrollment requests.
     */
    public function test_admin_dashboard_shows_pending_enrollments()
    {
        $admin = User::where('role', 'admin')->first();
        $student = User::where('role', 'student')->first();
        $course = Course::first();

        // Create a pending enrollment request
        Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'is_approved' => false,
        ]);

        // Request dashboard
        $response = $this->actingAs($admin)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pending Course Enrollment Requests');
        $response->assertSee($student->name);
        $response->assertSee($course->title);
    }
}
