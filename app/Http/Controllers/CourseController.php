<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
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
            $courses = Course::with(['teachers', 'schoolClass'])->get();
        } elseif ($user->isTeacher()) {
            // Fetch courses where teacher is creator OR assigned as course_admin/teacher
            $courseIds = array_unique(array_merge(
                Course::where('teacher_id', $user->id)->pluck('id')->toArray(),
                $user->assignedCourses()->pluck('course_id')->toArray()
            ));
            $courses = Course::whereIn('id', $courseIds)->with('schoolClass')->get();
        } else {
            // Student: can view all published courses that match their class (or have no class assigned)
            $courses = Course::where('is_published', true)
                ->where(function ($query) use ($user) {
                    $query->whereNull('school_class_id')
                          ->orWhere('school_class_id', $user->school_class_id);
                })
                ->with(['teachers', 'schoolClass'])
                ->get();
            // Get user enrollments map: course_id => is_approved
            $enrollmentsMap = $user->enrollments()->pluck('is_approved', 'course_id')->toArray();
            return view('courses.index', compact('courses', 'enrollmentsMap'));
        }

        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        if (!Auth::user()->hasPermission('create_courses')) {
            abort(403, 'Unauthorized.');
        }

        $classes = \App\Models\SchoolClass::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('courses.create', compact('classes', 'teachers'));
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('create_courses')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'teacher_id' => 'nullable|exists:users,id',
            'duration' => 'nullable|string|max:100',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $this->gcpService->upload($request->file('thumbnail'), 'thumbnails');
        }

        $creatorId = $user->isAdmin() && $request->filled('teacher_id') ? $request->teacher_id : $user->id;

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath,
            'teacher_id' => $creatorId,
            'school_class_id' => $request->school_class_id,
            'is_published' => $request->boolean('is_published'),
            'duration' => $request->duration,
        ]);

        // Auto-assign course creator as course_admin
        $course->teachers()->attach($creatorId, ['role' => 'course_admin']);

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
            if (!$user->isAssignedToCourse($course)) {
                abort(403, 'This course is not published.');
            }
        }

        // Load relations: subjects, modules, lectures, materials, live classes
        $course->load([
            'subjects.modules.lectures', 
            'subjects.modules.materials', 
            'subjects.liveClasses',
            'subjects.teachers',
            'teachers', 
            'schoolClass'
        ]);

        $isEnrolled = false;
        $isPending = false;
        if ($user->isStudent()) {
            $enrollment = \App\Models\Enrollment::where('student_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
            if ($enrollment) {
                $isEnrolled = $enrollment->is_approved;
                $isPending = !$enrollment->is_approved;
            }
        }

        // For teachers/admins, fetch pending enrollment requests for this course
        $pendingEnrollments = [];
        if ($user->isAssignedToCourse($course, 'course_admin')) {
            $pendingEnrollments = \App\Models\Enrollment::where('course_id', $course->id)
                ->where('is_approved', false)
                ->with('student')
                ->get();
        }

        $allTeachers = User::where('role', 'teacher')->orderBy('name')->get();

        // Progress tracking calculations
        $completedLectureIds = [];
        $lectureProgressMap = [];
        $courseProgressPercent = 0;
        $attendedLiveClassIds = [];
        $upcomingLiveClasses = [];
        $pastLiveClasses = [];

        // Collect all lectures in this course
        $courseLectureIds = [];
        foreach ($course->subjects as $subject) {
            foreach ($subject->modules as $mod) {
                foreach ($mod->lectures as $lec) {
                    $courseLectureIds[] = $lec->id;
                }
            }
        }

        if ($user->isStudent()) {
            $progressRecords = \App\Models\LectureProgress::where('user_id', $user->id)
                ->whereIn('lecture_id', $courseLectureIds)
                ->get();

            $completedCount = 0;
            foreach ($progressRecords as $rec) {
                if ($rec->is_completed) {
                    $completedLectureIds[] = $rec->lecture_id;
                    $completedCount++;
                }
                $lectureProgressMap[$rec->lecture_id] = $rec->seconds_watched;
            }

            $totalLectures = count($courseLectureIds);
            $courseProgressPercent = $totalLectures > 0 ? round(($completedCount / $totalLectures) * 100) : 0;

            $attendedLiveClassIds = \App\Models\LiveClassAttendance::where('user_id', $user->id)
                ->whereHas('liveClass.subject', function($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->pluck('live_class_id')
                ->toArray();
        }

        // Partition live classes by subject
        foreach ($course->subjects as $subject) {
            $upcomingLiveClasses[$subject->id] = [];
            $pastLiveClasses[$subject->id] = [];
            foreach ($subject->liveClasses as $lc) {
                if ($lc->datetime > now()) {
                    $upcomingLiveClasses[$subject->id][] = $lc;
                } else {
                    $pastLiveClasses[$subject->id][] = $lc;
                }
            }
        }

        return view('courses.show', compact(
            'course', 
            'isEnrolled', 
            'isPending', 
            'pendingEnrollments', 
            'allTeachers',
            'completedLectureIds',
            'lectureProgressMap',
            'courseProgressPercent',
            'attendedLiveClassIds',
            'upcomingLiveClasses',
            'pastLiveClasses'
        ));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        $user = Auth::user();
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized.');
        }

        $classes = \App\Models\SchoolClass::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('courses.edit', compact('course', 'classes', 'teachers'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $user = Auth::user();
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'teacher_id' => 'nullable|exists:users,id',
            'duration' => 'nullable|string|max:100',
        ]);

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'school_class_id' => $request->school_class_id,
            'is_published' => $request->boolean('is_published'),
            'duration' => $request->duration,
        ];

        if ($user->isAdmin() && $request->filled('teacher_id')) {
            $data['teacher_id'] = $request->teacher_id;
        }

        if ($request->hasFile('thumbnail')) {
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
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized.');
        }

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
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized.');
        }

        $course->update([
            'is_published' => !$course->is_published
        ]);

        $status = $course->is_published ? 'published' : 'unpublished';
        return redirect()->back()->with('success', "Course has been successfully {$status}.");
    }

    /**
     * Toggle course completion (Subject Completed batch-wise).
     */
    public function toggleCompletion(Course $course)
    {
        $user = Auth::user();
        if (!$user->isAssignedToCourse($course, 'course_admin')) {
            abort(403, 'Unauthorized.');
        }

        $course->update([
            'is_completed' => !$course->is_completed
        ]);

        $status = $course->is_completed ? 'completed' : 'active';
        return redirect()->back()->with('success', "Subject has been marked as {$status} for the class.");
    }

    /**
     * Assign teachers to a Course (Admin Only).
     */
    public function assignTeachers(Request $request, Course $course)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'teachers' => 'required|array',
            'teachers.*' => 'exists:users,id',
            'roles' => 'required|array',
        ]);

        $syncData = [];
        foreach ($request->teachers as $teacherId) {
            $role = $request->roles[$teacherId] ?? 'teacher';
            $syncData[$teacherId] = ['role' => $role];
        }

        $course->teachers()->sync($syncData);

        return redirect()->back()->with('success', 'Course teachers updated successfully.');
    }
}
