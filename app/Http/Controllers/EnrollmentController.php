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
        ]);

        return redirect()->route('courses.show', $course->id)->with('success', 'Enrolled in course successfully!');
    }

    /**
     * Unenroll a student from a course.
     */
    public function unenroll(Course $course)
    {
        $user = Auth::user();

        $enrollment = Enrollment::where('course_id', $course->id);

        if ($user->isStudent()) {
            $enrollment->where('student_id', $user->id);
        } elseif (!$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $record = $enrollment->first();

        if ($record) {
            $record->delete();
            return redirect()->route('courses.index')->with('success', 'Unenrolled from course successfully.');
        }

        return redirect()->back()->with('error', 'Enrollment record not found.');
    }
}
