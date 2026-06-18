<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecture;
use App\Models\LiveClass;
use App\Models\Module;
use App\Models\User;
use App\Models\ClassNote;
use App\Models\LoginHistory;
use App\Models\UsageLog;
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
                'total_classes' => \App\Models\SchoolClass::count(),
            ];
            
            // Build paginated, filtered, searched query for users
            $query = User::with('schoolClass');
            
            if (request()->filled('search')) {
                $search = request()->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            if (request()->filled('role')) {
                $query->where('role', request()->input('role'));
            }
            
            if (request()->filled('class_id')) {
                $query->where('school_class_id', request()->input('class_id'));
            }
            
            if (request()->filled('status')) {
                $status = request()->input('status');
                if ($status === 'approved') {
                    $query->where('is_approved', true);
                } elseif ($status === 'pending') {
                    $query->where('is_approved', false);
                }
            }
            
            $users = $query->orderBy('name')->paginate(10)->withQueryString();
            
            $courses = Course::with('teachers')->get();
            $classes = \App\Models\SchoolClass::orderBy('name')->get();
            $pendingEnrollments = Enrollment::where('is_approved', false)
                ->with(['student', 'course'])
                ->get();

            return view('dashboard.admin', compact('stats', 'users', 'courses', 'classes', 'pendingEnrollments'));
        }

        if ($user->isTeacher()) {
            // Fetch courses where teacher is creator OR assigned
            $myCourseIds = array_unique(array_merge(
                Course::where('teacher_id', $user->id)->pluck('id')->toArray(),
                $user->assignedCourses()->pluck('course_id')->toArray()
            ));
            
            // Fetch subject ids under these courses
            $mySubjectIds = \App\Models\Subject::whereIn('course_id', $myCourseIds)->pluck('id')->toArray();
            
            // Include subjects directly assigned to the teacher
            $mySubjectIds = array_unique(array_merge(
                $mySubjectIds,
                $user->assignedSubjects()->pluck('subject_id')->toArray()
            ));

            $stats = [
                'courses_count' => count($myCourseIds),
                'lectures_count' => Lecture::whereHas('module', function($q) use ($mySubjectIds) {
                    $q->whereIn('subject_id', $mySubjectIds);
                })->count(),
                'upcoming_classes_count' => LiveClass::whereIn('subject_id', $mySubjectIds)
                    ->where('datetime', '>', now())
                    ->count(),
            ];

            $courses = Course::whereIn('id', $myCourseIds)->get();
            $upcomingClasses = LiveClass::whereIn('subject_id', $mySubjectIds)
                ->where('datetime', '>', now())
                ->orderBy('datetime')
                ->get();

            $pendingEnrollments = Enrollment::whereIn('course_id', $myCourseIds)
                ->where('is_approved', false)
                ->with(['student', 'course'])
                ->get();

            // Fetch classes assigned to this teacher to allow sending announcements
            $myClasses = $user->assignedClasses()->orderBy('name')->get();

            // Fetch announcements created by this teacher
            $classNotes = ClassNote::where('teacher_id', $user->id)
                ->with('schoolClass')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('dashboard.teacher', compact('stats', 'courses', 'upcomingClasses', 'pendingEnrollments', 'myClasses', 'classNotes'));
        }

        if ($user->isStudent()) {
            // Get courses with approved enrollments
            $approvedCourses = $user->enrolledCourses()
                ->wherePivot('is_approved', true)
                ->with('teachers')
                ->get();
            
            // Get courses assigned to the student's class (if any)
            $classCourses = collect();
            if ($user->school_class_id) {
                $classCourses = Course::where('school_class_id', $user->school_class_id)
                    ->where('is_published', true)
                    ->with('teachers')
                    ->get();
            }

            // Union of approved courses and class-assigned courses
            $enrolledCourses = $approvedCourses->merge($classCourses)->unique('id');
            $enrolledCourseIds = $enrolledCourses->pluck('id')->toArray();

            // Fetch subjects in enrolled courses
            $enrolledSubjectIds = \App\Models\Subject::whereIn('course_id', $enrolledCourseIds)->pluck('id')->toArray();

            $stats = [
                'enrolled_count' => count($enrolledCourseIds),
                'upcoming_classes_count' => LiveClass::whereIn('subject_id', $enrolledSubjectIds)
                    ->where('datetime', '>', now())
                    ->count(),
            ];

            $upcomingClasses = LiveClass::whereIn('subject_id', $enrolledSubjectIds)
                ->where('datetime', '>', now())
                ->orderBy('datetime')
                ->get();

            // Fetch noticeboard announcements for this student's class OR global (all classes)
            $classNotes = ClassNote::where(function($q) use ($user) {
                $q->whereNull('school_class_id');
                if ($user->school_class_id) {
                    $q->orWhere('school_class_id', $user->school_class_id);
                }
            })
            ->with('teacher')
            ->orderBy('created_at', 'desc')
            ->get();

            return view('dashboard.student', compact('stats', 'enrolledCourses', 'upcomingClasses', 'classNotes'));
        }

        abort(403, 'Unauthorized.');
    }

    /**
     * User Directory endpoint accessible by Admins and delegated profile managers.
     */
    public function userDirectory(Request $request)
    {
        $caller = Auth::user();
        
        $canManageStudents = $caller->isAdmin() || $caller->hasPermission('manage_student_profiles');
        $canManageTeachers = $caller->isAdmin() || $caller->hasPermission('manage_teacher_profiles');

        if (!$canManageStudents && !$canManageTeachers) {
            abort(403, 'Unauthorized.');
        }

        $query = User::with('schoolClass');

        // Scoping based on permissions
        if (!$caller->isAdmin()) {
            $query->where('role', '!=', 'admin'); // Non-admins can never see/manage admins
            
            if ($canManageStudents && !$canManageTeachers) {
                $query->where('role', 'student');
            } elseif ($canManageTeachers && !$canManageStudents) {
                $query->where('role', 'teacher');
            } else {
                $query->whereIn('role', ['student', 'teacher']);
            }
        }

        // Search and filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('role')) {
            $roleFilter = $request->input('role');
            // Ensure delegated manager cannot bypass their role scope via query string filter
            if (!$caller->isAdmin()) {
                if ($roleFilter === 'admin' || 
                    ($roleFilter === 'student' && !$canManageStudents) || 
                    ($roleFilter === 'teacher' && !$canManageTeachers)) {
                    abort(403, 'Unauthorized filter role.');
                }
            }
            $query->where('role', $roleFilter);
        }
        
        if ($request->filled('class_id')) {
            $query->where('school_class_id', $request->input('class_id'));
        }
        
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($status === 'pending') {
                $query->where('is_approved', false);
            }
        }
        
        $users = $query->orderBy('name')->paginate(10)->withQueryString();
        $classes = \App\Models\SchoolClass::orderBy('name')->get();
        $courses = Course::with('teachers')->get();

        return view('admin.users.index', compact('users', 'classes', 'courses'));
    }

    /**
     * Display the profile audit dashboard for a specific user.
     */
    public function userProfile(User $user)
    {
        $caller = Auth::user();

        if (!$caller->isAdmin()) {
            if ($user->isStudent() && !$caller->hasPermission('manage_student_profiles')) {
                abort(403, 'Unauthorized.');
            }
            if ($user->isTeacher() && !$caller->hasPermission('manage_teacher_profiles')) {
                abort(403, 'Unauthorized.');
            }
            if ($user->isAdmin()) {
                abort(403, 'Unauthorized.');
            }
        }

        // Load relations
        $user->load(['schoolClass', 'loginHistories', 'usageLogs.course']);

        // Enrolled courses for students
        $enrolledCourses = [];
        if ($user->isStudent()) {
            $enrolledCourses = $user->enrolledCourses()->with('teachers')->get();
        }

        // Taught courses for teachers
        $taughtCourses = [];
        if ($user->isTeacher()) {
            $courseIds = array_unique(array_merge(
                Course::where('teacher_id', $user->id)->pluck('id')->toArray(),
                $user->assignedCourses()->pluck('course_id')->toArray()
            ));
            $taughtCourses = Course::whereIn('id', $courseIds)->get();
        }

        // Fetch logs
        $loginHistories = $user->loginHistories()->orderBy('logged_in_at', 'desc')->get();
        $usageLogs = $user->usageLogs()->orderBy('created_at', 'desc')->get();

        return view('admin.users.profile', compact('user', 'enrolledCourses', 'taughtCourses', 'loginHistories', 'usageLogs'));
    }

    /**
     * Update user role (Strictly Admin only).
     */
    public function updateUserRole(Request $request, User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'role' => 'required|in:admin,teacher,student',
        ]);

        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'You cannot change your own admin role.');
        }

        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', "User role updated to {$request->role} successfully.");
    }

    /**
     * Create a new user with a specific role.
     */
    public function createUser(Request $request)
    {
        $caller = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
        ]);

        // Access check
        $role = $request->role;
        if ($role === 'student') {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_student_profiles')) {
                abort(403, 'Unauthorized to create student.');
            }
        } elseif ($role === 'teacher') {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_teacher_profiles')) {
                abort(403, 'Unauthorized to create teacher.');
            }
        } else {
            // role === 'admin'
            if (!$caller->isAdmin()) {
                abort(403, 'Unauthorized to create admin.');
            }
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'school_class_id' => $request->school_class_id,
            'is_approved' => true,
        ]);

        return redirect()->back()->with('success', "New {$request->role} user registered successfully.");
    }

    /**
     * Toggle the approval status of a user.
     */
    public function toggleApproval(User $user)
    {
        $caller = Auth::user();

        if ($user->id === $caller->id) {
            return redirect()->back()->with('error', 'You cannot change your own approval status.');
        }

        if ($user->isStudent()) {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_student_profiles')) {
                abort(403, 'Unauthorized.');
            }
        } elseif ($user->isTeacher()) {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_teacher_profiles')) {
                abort(403, 'Unauthorized.');
            }
        } else {
            // target is admin
            if (!$caller->isAdmin()) {
                abort(403, 'Unauthorized.');
            }
        }

        $user->update([
            'is_approved' => !$user->is_approved
        ]);

        $status = $user->is_approved ? 'approved' : 'unapproved';
        return redirect()->back()->with('success', "User account has been {$status} successfully.");
    }

    /**
     * Toggle the suspension status of a user.
     */
    public function toggleSuspend(User $user)
    {
        $caller = Auth::user();

        if ($user->id === $caller->id) {
            return redirect()->back()->with('error', 'You cannot change your own suspension status.');
        }

        if ($user->isStudent()) {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_student_profiles')) {
                abort(403, 'Unauthorized.');
            }
        } elseif ($user->isTeacher()) {
            if (!$caller->isAdmin() && !$caller->hasPermission('manage_teacher_profiles')) {
                abort(403, 'Unauthorized.');
            }
        } else {
            // target is admin
            if (!$caller->isAdmin()) {
                abort(403, 'Unauthorized.');
            }
        }

        $user->update([
            'is_suspended' => !$user->is_suspended
        ]);

        $status = $user->is_suspended ? 'suspended' : 'activated';
        return redirect()->back()->with('success', "User account has been {$status} successfully.");
    }

    /**
     * Bulk register students from copy-pasted Name, Email list.
     */
    public function bulkCreateUsers(Request $request)
    {
        $caller = Auth::user();
        if (!$caller->isAdmin() && !$caller->hasPermission('manage_student_profiles')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'bulk_data' => 'required|string',
        ]);

        $lines = explode("\n", $request->bulk_data);
        $successCount = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = str_getcsv($line);
            
            if (count($parts) < 2) {
                $errors[] = "Line " . ($index + 1) . ": Invalid format. Must be 'Name, Email'.";
                continue;
            }

            $name = trim($parts[0]);
            $email = trim($parts[1]);
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Line " . ($index + 1) . ": Invalid email format ({$email}).";
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $errors[] = "Line " . ($index + 1) . ": Email already registered ({$email}).";
                continue;
            }

            User::create([
                'name' => $name,
                'email' => $email,
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'student',
                'is_approved' => true,
            ]);
            $successCount++;
        }

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('success', "Bulk registration complete. {$successCount} students registered.")
                ->withErrors($errors);
        }

        return redirect()->back()->with('success', "Bulk registration complete! Successfully registered {$successCount} students.");
    }
}
