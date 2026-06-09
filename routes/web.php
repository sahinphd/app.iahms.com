<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LiveClassController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ModuleController;
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
        Route::get('/courses/create/new', [CourseController::class, 'create'])->name('courses.create'); // Specific path to avoid conflict with show resource
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('/courses/{course}/toggle-publish', [CourseController::class, 'togglePublish'])->name('courses.toggle-publish');

        // Module Management
        Route::post('/modules', [ModuleController::class, 'store'])->name('modules.store');
        Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update');
        Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy');

        // Lecture Management
        Route::post('/lectures', [LectureController::class, 'store'])->name('lectures.store');
        Route::delete('/lectures/{lecture}', [LectureController::class, 'destroy'])->name('lectures.destroy');

        // Material Management
        Route::post('/materials', [MaterialController::class, 'store'])->name('materials.store');
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

        // Live Class Management
        Route::post('/live-classes', [LiveClassController::class, 'store'])->name('live-classes.store');
        Route::put('/live-classes/{liveClass}', [LiveClassController::class, 'update'])->name('live-classes.update');
        Route::delete('/live-classes/{liveClass}', [LiveClassController::class, 'destroy'])->name('live-classes.destroy');
    });

    // Student specific routes
    Route::middleware('role:student')->group(function () {
        Route::post('/enroll', [EnrollmentController::class, 'enroll'])->name('enrollments.enroll');
    });

    // Student self-unenrollment or Admin unenrollment
    Route::delete('/unenroll/{course}', [EnrollmentController::class, 'unenroll'])->name('enrollments.unenroll');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::put('/admin/users/{user}/role', [DashboardController::class, 'updateUserRole'])->name('admin.users.update-role');
        Route::post('/admin/users', [DashboardController::class, 'createUser'])->name('admin.users.create');
    });
});
