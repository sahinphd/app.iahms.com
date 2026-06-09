<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Store a newly created subject in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        // Check course assignment or admin status
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized. Only Course Administrators can add subjects.');
        }

        Subject::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
        ]);

        return redirect()->back()->with('success', 'Subject added successfully!');
    }

    /**
     * Remove the specified subject.
     */
    public function destroy(Subject $subject)
    {
        $user = Auth::user();

        if (!$user->isAssignedToCourse($subject->course, 'course_admin')) {
            abort(403, 'Unauthorized. Only Course Administrators can delete subjects.');
        }

        $subject->delete();

        return redirect()->back()->with('success', 'Subject deleted successfully.');
    }

    /**
     * Assign teachers to a specific subject (Admin or Course Admin of parent course).
     */
    public function assignTeachers(Request $request, Subject $subject)
    {
        $user = Auth::user();

        if (!$user->isAssignedToCourse($subject->course, 'course_admin')) {
            abort(403, 'Unauthorized. Only Course Administrators can assign teachers to subjects.');
        }

        $request->validate([
            'teachers' => 'required|array',
            'teachers.*' => 'exists:users,id',
            'roles' => 'required|array',
        ]);

        // Sync teachers
        $syncData = [];
        foreach ($request->teachers as $teacherId) {
            $role = $request->roles[$teacherId] ?? 'teacher';
            $syncData[$teacherId] = ['role' => $role];
        }

        $subject->teachers()->sync($syncData);

        return redirect()->back()->with('success', 'Teachers assigned to subject successfully.');
    }
}
