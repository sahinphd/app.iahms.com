<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\GoogleCloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    protected $gcpService;

    public function __construct(GoogleCloudStorageService $gcpService)
    {
        $this->gcpService = $gcpService;
    }

    /**
     * Display a listing of courses.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $courses = Course::with('teacher')->get();
        } elseif ($user->isTeacher()) {
            $courses = Course::where('teacher_id', $user->id)->get();
        } else {
            // Student: can view all published courses
            $courses = Course::where('is_published', true)->with('teacher')->get();
            // Get enrolled course IDs for checking enrollment status in views
            $enrolledCourseIds = $user->enrolledCourses()->pluck('courses.id')->toArray();
            return view('courses.index', compact('courses', 'enrolledCourseIds'));
        }

        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        // Only teachers and admins
        if (!Auth::user()->isAdmin() && !Auth::user()->isTeacher()) {
            abort(403, 'Unauthorized.');
        }

        return view('courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isTeacher()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            // Storing thumbnail inside course directory path (using mock GCP service)
            $thumbnailPath = $this->gcpService->upload($request->file('thumbnail'), 'thumbnails');
        }

        Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath,
            'teacher_id' => $user->isAdmin() && $request->filled('teacher_id') ? $request->teacher_id : $user->id,
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('courses.index')->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $user = Auth::user();

        // Access check
        if (!$course->is_published) {
            if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
                abort(403, 'This course is not published.');
            }
        }

        // Load relations
        $course->load(['modules.lectures', 'modules.materials', 'liveClasses', 'teacher']);

        $isEnrolled = false;
        if ($user->isStudent()) {
            $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
        }

        return view('courses.show', compact('course', 'isEnrolled'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'is_published' => $request->boolean('is_published'),
        ];

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($course->thumbnail) {
                $this->gcpService->delete($course->thumbnail);
            }
            $data['thumbnail'] = $this->gcpService->upload($request->file('thumbnail'), 'thumbnails');
        }

        $course->update($data);

        return redirect()->route('courses.show', $course->id)->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        // Delete thumbnail if exists
        if ($course->thumbnail) {
            $this->gcpService->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Course deleted successfully.');
    }

    /**
     * Publish/Unpublish a course.
     */
    public function togglePublish(Course $course)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $course->update([
            'is_published' => !$course->is_published
        ]);

        $status = $course->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Course has been successfully {$status}.");
    }
}
