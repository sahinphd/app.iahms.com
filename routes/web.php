<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LiveClassController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ClassNoteController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;

// Redirect home to dashboard or login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Authentication Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes (Requires Login)
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Local serve secure media files route
    Route::get('/local-serve/{path}', function (\Illuminate\Http\Request $request, $path) {
        if (! $request->hasValidSignature()) {
            abort(403, 'Expired or invalid signature.');
        }
        $fullPath = storage_path("app/public/{$path}");
        if (!file_exists($fullPath)) {
            abort(404, 'File not found on local disk.');
        }
        return response()->file($fullPath);
    })->name('local.storage.serve')->where('path', '.*');

    // Courses - Public/Student viewing
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

    // Secure Streaming and Downloading (access checks inside controllers)
    Route::get('/lectures/{lecture}', [LectureController::class, 'show'])->name('lectures.show');
    Route::get('/lectures/{lecture}/stream', [LectureController::class, 'getStreamUrl'])->name('lectures.stream');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    // Teacher & Admin restricted routes
    Route::middleware('role:teacher,admin')->group(function () {
        // Course Management CRUD
        Route::get('/courses/create/new', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/toggle-publish', [CourseController::class, 'togglePublish'])->name('courses.toggle-publish');
        Route::post('/courses/{course}/toggle-completion', [CourseController::class, 'toggleCompletion'])->name('courses.toggle-completion');

        // Subject Management
        Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
        Route::post('/subjects/{subject}/assign-teachers', [SubjectController::class, 'assignTeachers'])->name('subjects.assign-teachers');

        // Module Management
        Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
        Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');

        // Lecture Management
        Route::post('/lectures', [LectureController::class, 'store'])->name('lectures.store');
        Route::post('/lectures/generate-upload-url', [LectureController::class, 'generateUploadUrl'])->name('lectures.generate-upload-url');
        Route::delete('/lectures/{lecture}', [LectureController::class, 'destroy'])->name('lectures.destroy');

        Route::put('/lectures/direct-upload-local/{path}', [LectureController::class, 'localDirectUpload'])
            ->name('local.storage.direct-upload')
            ->where('path', '.*');

        // Material Management
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

        // Live Class Management
        Route::post('/live-classes', [LiveClassController::class, 'store'])->name('live-classes.store');
        Route::put('/live-classes/{liveClass}', [LiveClassController::class, 'update'])->name('live-classes.update');
        Route::delete('/live-classes/{liveClass}', [LiveClassController::class, 'destroy'])->name('live-classes.destroy');

        // Class Notes Management
        Route::post('/teacher/class-notes', [ClassNoteController::class, 'store'])->name('teacher.class-notes.store');
        Route::delete('/teacher/class-notes/{classNote}', [ClassNoteController::class, 'destroy'])->name('teacher.class-notes.destroy');

        // Enrollment Approvals
        Route::post('/enrollments/{enrollment}/approve', [EnrollmentController::class, 'approve'])->name('enrollments.approve');
    });

    // Student specific routes
    Route::middleware('role:student')->group(function () {
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])->name('enrollments.enroll');
    });

    // Student self-unenrollment or Admin unenrollment
    Route::delete('/unenroll/{course}', [EnrollmentController::class, 'unenroll'])->name('enrollments.unenroll');

    // Lecture Progress and Live Class Join redirect routes
    Route::post('/lectures/{lecture}/progress', [LectureController::class, 'updateProgress'])->name('lectures.progress');
    Route::get('/live-classes/{liveClass}/join', [LiveClassController::class, 'join'])->name('live-classes.join');
    Route::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');

    // User Directory and User Profile management (Dynamically authorized in controller)
    Route::get('/admin/users', [DashboardController::class, 'userDirectory'])->name('admin.users.index');
    Route::post('/admin/users', [DashboardController::class, 'createUser'])->name('admin.users.create');
    Route::put('/admin/users/{user}/role', [DashboardController::class, 'updateUserRole'])->name('admin.users.update-role');
    Route::post('/admin/users/{user}/toggle-approval', [DashboardController::class, 'toggleApproval'])->name('admin.users.toggle-approval');
    Route::post('/admin/users/{user}/toggle-suspend', [DashboardController::class, 'toggleSuspend'])->name('admin.users.toggle-suspend');
    Route::post('/admin/users/bulk', [DashboardController::class, 'bulkCreateUsers'])->name('admin.users.bulk');
    Route::get('/admin/users/{user}/profile', [DashboardController::class, 'userProfile'])->name('admin.users.profile');

    // Dynamic Teacher assignments (authorized inside controllers)
    Route::post('/admin/courses/{course}/assign-teachers', [CourseController::class, 'assignTeachers'])->name('admin.courses.assign-teachers');
    Route::post('/admin/classes/{schoolClass}/assign-teachers', [ClassController::class, 'assignTeachers'])->name('admin.classes.assign-teachers');
    Route::get('/classes/{schoolClass}', [ClassController::class, 'show'])->name('classes.show');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Theme settings
        Route::get('/admin/theme', [SettingController::class, 'showTheme'])->name('admin.theme');
        Route::post('/admin/theme', [SettingController::class, 'updateTheme'])->name('admin.theme.update');

        // Storage settings
        Route::get('/admin/settings/storage', [SettingController::class, 'showStorageSettings'])->name('admin.settings.storage');
        Route::post('/admin/settings/storage', [SettingController::class, 'updateStorageSettings'])->name('admin.settings.storage.update');
    });

    // Admin features protected by dynamic permissions
    Route::middleware('permission:manage_classes')->group(function () {
        Route::get('/admin/classes', [ClassController::class, 'index'])->name('admin.classes.index');
        Route::post('/admin/classes', [ClassController::class, 'store'])->name('admin.classes.store');
        Route::delete('/admin/classes/{schoolClass}', [ClassController::class, 'destroy'])->name('admin.classes.destroy');
        Route::post('/admin/classes/assign-student', [ClassController::class, 'assignStudent'])->name('admin.classes.assign-student');
        Route::post('/admin/classes/assign-teacher', [ClassController::class, 'assignTeacher'])->name('admin.classes.assign-teacher');
        Route::post('/admin/classes/remove-teacher', [ClassController::class, 'removeTeacher'])->name('admin.classes.remove-teacher');
        Route::post('/admin/classes/allot-students-bulk', [ClassController::class, 'allotStudentsBulk'])->name('admin.classes.allot-students-bulk');
    });

    Route::middleware('permission:manage_role_permissions')->group(function () {
        Route::get('/admin/permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
        Route::post('/admin/permissions/toggle', [PermissionController::class, 'toggle'])->name('admin.permissions.toggle');
        Route::get('/admin/permissions/user/{user}', [PermissionController::class, 'userPermissions'])->name('admin.permissions.user');
        Route::post('/admin/permissions/user/{user}/toggle', [PermissionController::class, 'userToggle'])->name('admin.permissions.user.toggle');
    });

    Route::middleware('permission:view_reports')->group(function () {
        Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    });
});
