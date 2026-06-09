<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    /**
     * Enroll the authenticated student in a course.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = Auth::user();

        if (!$user->isStudent()) {
            return redirect()->back()->with('error', 'Only students can enroll in courses.');
        }

        $course = Course::findOrFail($request->course_id);

        if (!$course->is_published) {
            return redirect()->back()->with('error', 'Cannot enroll in an unpublished course.');
        }

        // Check if already enrolled
        $exists = Enrollment::where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'You are already enrolled in this course.');
        }

        Enrollment::create([
            'student_id' => $user->id,
            'course_id' => $course->id,
            'is_approved' => false, // Student enrollments must be approved by teacher/admin
        ]);

        return redirect()->route('courses.show', $course->id)->with('success', 'Enrollment request submitted successfully! Waiting for approval from the instructor or administrator.');
    }

    /**
     * Approve a pending enrollment.
     */
    public function approve(Enrollment $enrollment)
    {
        $user = Auth::user();
        $course = $enrollment->course;

        // Must be admin, or the teacher of this course
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $enrollment->update([
            'is_approved' => true,
        ]);

        return redirect()->back()->with('success', 'Student enrollment approved successfully!');
    }

    /**
     * Unenroll a student from a course.
     */
    public function unenroll(Course $course, Request $request)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('course_id', $course->id);

        if ($user->isStudent()) {
            $enrollment->where('student_id', $user->id);
        } else {
            // Must be admin, or the teacher of this course
            if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
                abort(403, 'Unauthorized.');
            }

            $request->validate([
                'student_id' => 'required|exists:users,id',
            ]);

            $enrollment->where('student_id', $request->student_id);
        }

        $record = $enrollment->first();

        if ($record) {
            $record->delete();
            return redirect()->back()->with('success', 'Enrollment removed/rejected successfully.');
        }

        return redirect()->back()->with('error', 'Enrollment record not found.');
    }
}
