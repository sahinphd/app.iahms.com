<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecture;
use App\Models\LiveClass;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'total_courses' => Course::count(),
                'total_students' => User::where('role', 'student')->count(),
                'total_teachers' => User::where('role', 'teacher')->count(),
            ];
            // Get all users for admin management page
            $users = User::orderBy('name')->get();
            $courses = Course::with('teacher')->get();

            return view('dashboard.admin', compact('stats', 'users', 'courses'));
        }

        if ($user->isTeacher()) {
            $myCourseIds = Course::where('teacher_id', $user->id)->pluck('id')->toArray();
            $myModuleIds = Module::whereIn('course_id', $myCourseIds)->pluck('id')->toArray();

            $stats = [
                'courses_count' => count($myCourseIds),
                'lectures_count' => Lecture::whereIn('module_id', $myModuleIds)->count(),
                'upcoming_classes_count' => LiveClass::whereIn('course_id', $myCourseIds)
                    ->where('datetime', '>', now())
                    ->count(),
            ];

            $courses = Course::where('teacher_id', $user->id)->get();
            $upcomingClasses = LiveClass::whereIn('course_id', $myCourseIds)
                ->where('datetime', '>', now())
                ->orderBy('datetime')
                ->get();

            return view('dashboard.teacher', compact('stats', 'courses', 'upcomingClasses'));
        }

        if ($user->isStudent()) {
            $enrolledCourses = $user->enrolledCourses()->with('teacher')->get();
            $enrolledCourseIds = $enrolledCourses->pluck('id')->toArray();

            $stats = [
                'enrolled_count' => count($enrolledCourseIds),
                'upcoming_classes_count' => LiveClass::whereIn('course_id', $enrolledCourseIds)
                    ->where('datetime', '>', now())
                    ->count(),
            ];

            $upcomingClasses = LiveClass::whereIn('course_id', $enrolledCourseIds)
                ->where('datetime', '>', now())
                ->orderBy('datetime')
                ->get();

            return view('dashboard.student', compact('stats', 'enrolledCourses', 'upcomingClasses'));
        }

        abort(403, 'Unauthorized.');
    }

    /**
     * Admin capability: Update user role or delete user.
     */
    public function updateUserRole(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'role' => 'required|in:admin,teacher,student',
        ]);

        // Prevent self demotion
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'You cannot change your own admin role.');
        }

        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', "User role updated to {$request->role} successfully.");
    }

    /**
     * Admin capability: Create a new user with a specific role.
     */
    public function createUser(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', "New {$request->role} user registered successfully.");
    }
}
