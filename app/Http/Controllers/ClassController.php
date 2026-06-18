<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    /**
     * Display a list of school classes and student/teacher assignments.
     */
    public function index()
    {
        $classes = SchoolClass::with(['students', 'courses.subjects', 'teachers'])
            ->withCount(['students', 'courses'])
            ->get();
        
        // Fetch users who are students or teachers, with their current class
        $students = User::where('role', 'student')->with('schoolClass')->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->with(['schoolClass', 'assignedClasses'])->orderBy('name')->get();
        $allTeachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('admin.classes.index', compact('classes', 'students', 'teachers', 'allTeachers'));
    }

    /**
     * Store a newly created school class.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:school_classes,name',
            'description' => 'nullable|string',
        ]);

        SchoolClass::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Class created successfully.');
    }

    /**
     * Remove the specified school class.
     */
    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();

        return redirect()->back()->with('success', 'Class deleted successfully.');
    }

    /**
     * Allot a student to a class (Admin or Class Admin).
     */
    public function assignStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'school_class_id' => 'nullable|exists:school_classes,id',
        ]);

        $student = User::where('role', 'student')->findOrFail($request->student_id);
        $student->update([
            'school_class_id' => $request->school_class_id,
        ]);

        $status = $request->school_class_id ? 'assigned to class' : 'unassigned from class';
        return redirect()->back()->with('success', "Student '{$student->name}' was successfully {$status}.");
    }

    /**
     * Allot a teacher to a class (Admin Only).
     */
    public function assignTeacher(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'role' => 'required|in:teacher,class_admin',
        ]);

        $teacher = User::where('role', 'teacher')->findOrFail($request->teacher_id);
        $class = SchoolClass::findOrFail($request->school_class_id);

        $class->teachers()->syncWithoutDetaching([
            $teacher->id => ['role' => $request->role]
        ]);

        // Force update the pivot role if already attached
        $class->teachers()->updateExistingPivot($teacher->id, ['role' => $request->role]);

        return redirect()->back()->with('success', "Teacher '{$teacher->name}' was allotted to class '{$class->name}' as '{$request->role}'.");
    }

    /**
     * Remove a teacher assignment from a class (Admin Only).
     */
    public function removeTeacher(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'school_class_id' => 'required|exists:school_classes,id',
        ]);

        $class = SchoolClass::findOrFail($request->school_class_id);
        $class->teachers()->detach($request->teacher_id);

        return redirect()->back()->with('success', "Teacher assignment removed successfully.");
    }
    /**
     * Bulk allot students to a class (Admin Only).
     */
    public function allotStudentsBulk(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'students' => 'nullable|array',
            'students.*' => 'exists:users,id',
        ]);

        $classId = $request->school_class_id;
        $studentIds = $request->input('students', []);

        // 1. Remove all students currently in this class
        User::where('school_class_id', $classId)
            ->where('role', 'student')
            ->update(['school_class_id' => null]);

        // 2. Allot selected students to this class
        if (count($studentIds) > 0) {
            User::whereIn('id', $studentIds)
                ->where('role', 'student')
                ->update(['school_class_id' => $classId]);
        }

        return redirect()->back()->with('success', 'Student class allotments updated successfully!');
    }
    public function assignTeachers(Request $request, SchoolClass $schoolClass)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isAssignedToClass($schoolClass, 'class_admin')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'teachers' => 'nullable|array',
            'teachers.*' => 'exists:users,id',
            'roles' => 'nullable|array',
        ]);

        $syncData = [];
        $teacherIds = $request->input('teachers', []);
        foreach ($teacherIds as $teacherId) {
            $role = $request->roles[$teacherId] ?? 'teacher';
            $syncData[$teacherId] = ['role' => $role];
        }

        $schoolClass->teachers()->sync($syncData);

        return redirect()->back()->with('success', 'Class teachers updated successfully.');
    }

    /**
     * Display the specified school class with its students and teachers.
     */
    public function show(SchoolClass $schoolClass)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isAssignedToClass($schoolClass)) {
            abort(403, 'Unauthorized to view this class.');
        }

        $schoolClass->load(['students', 'teachers', 'courses']);
        $canManageClass = $user->isAdmin() || $user->isAssignedToClass($schoolClass, 'class_admin');

        $allTeachers = [];
        if ($canManageClass) {
            $allTeachers = User::where('role', 'teacher')->orderBy('name')->get();
        }

        return view('classes.show', compact('schoolClass', 'allTeachers', 'canManageClass'));
    }
}
