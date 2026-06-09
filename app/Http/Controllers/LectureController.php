<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Module;
use App\Services\Storage\StorageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    protected $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * Store a newly created lecture in storage (upload video).
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'duration' => 'nullable|string|max:100',
            'video' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-la-asf,video/x-ms-asf,video/x-ms-wmv,video/x-msvideo,video/x-sgi-movie|max:102400', // max 100MB
        ]);

        $module = Module::findOrFail($request->module_id);
        $subject = $module->subject;
        $course = $subject->course;
        $user = Auth::user();

        // Check access: user has permission and is assigned to this subject (or is admin)
        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage its syllabus.');
        }

        if ($request->hasFile('video')) {
            // Upload path: /videos/course-id/
            $destinationPath = "videos/{$course->id}";
            $uploadedFilePath = $this->storageManager->driver()->upload($request->file('video'), $destinationPath);

            Lecture::create([
                'module_id' => $module->id,
                'title' => $request->title,
                'file_path' => $uploadedFilePath,
                'duration' => $request->duration,
            ]);

            return redirect()->back()->with('success', 'Lecture video uploaded successfully.');
        }

        return redirect()->back()->withErrors(['video' => 'Failed to upload video file.']);
    }

    /**
     * Show the video player page.
     */
    public function show(Lecture $lecture)
    {
        $user = Auth::user();
        $module = $lecture->module;
        $subject = $module->subject;
        $course = $subject->course;

        // Check access: Admin or assigned subject teacher or enrolled student
        if (!$user->isAssignedToSubject($subject)) {
            $isEnrolled = $user->enrolledCourses()
                ->where('course_id', $course->id)
                ->where('enrollments.is_approved', true)
                ->exists();
            if (!$isEnrolled) {
                abort(403, 'You must have an approved enrollment in this course to watch this lecture.');
            }
        }

        if ($user->isStudent()) {
            \App\Models\UsageLog::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'action' => 'watch_video',
                'details' => "Watched video lecture: {$lecture->title}",
            ]);
        }

        $progress = \App\Models\LectureProgress::where('user_id', $user->id)
            ->where('lecture_id', $lecture->id)
            ->first();
        $initialSecondsWatched = $progress ? $progress->seconds_watched : 0;

        // Fetch completed lecture IDs for chest layout sidebar checkmarks
        $completedLectureIds = \App\Models\LectureProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereHas('lecture.module.subject', function($q) use ($course) {
                $q->where('course_id', $course->id);
            })
            ->pluck('lecture_id')
            ->toArray();

        return view('lectures.show', compact('lecture', 'course', 'initialSecondsWatched', 'completedLectureIds'));
    }

    /**
     * Update/Upsert the user's progress tracking for a lecture.
     */
    public function updateProgress(Request $request, Lecture $lecture)
    {
        $request->validate([
            'seconds_watched' => 'required|integer|min:0',
            'is_completed' => 'required|boolean',
        ]);

        $user = Auth::user();
        $module = $lecture->module;
        $subject = $module->subject;
        $course = $subject->course;

        // Check access
        if (!$user->isAssignedToSubject($subject)) {
            $isEnrolled = $user->enrolledCourses()
                ->where('course_id', $course->id)
                ->where('enrollments.is_approved', true)
                ->exists();
            if (!$isEnrolled) {
                return response()->json(['error' => 'Unauthorized.'], 403);
            }
        }

        $progress = \App\Models\LectureProgress::firstOrNew([
            'user_id' => $user->id,
            'lecture_id' => $lecture->id,
        ]);

        $wasCompletedBefore = $progress->is_completed;

        $progress->seconds_watched = max($progress->seconds_watched, $request->seconds_watched);
        $progress->is_completed = $wasCompletedBefore || $request->is_completed;
        $progress->last_watched_at = now();
        $progress->save();

        if ($user->isStudent() && !$wasCompletedBefore && $progress->is_completed) {
            \App\Models\UsageLog::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'action' => 'completed_lecture',
                'details' => "Completed video lecture: {$lecture->title}",
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get the signed URL for the video (JSON response).
     */
    public function getStreamUrl(Lecture $lecture)
    {
        $user = Auth::user();
        $module = $lecture->module;
        $subject = $module->subject;
        $course = $subject->course;

        // Check access: Admin or assigned subject teacher or enrolled student
        if (!$user->isAssignedToSubject($subject)) {
            $isEnrolled = $user->enrolledCourses()
                ->where('course_id', $course->id)
                ->where('enrollments.is_approved', true)
                ->exists();
            if (!$isEnrolled) {
                return response()->json(['error' => 'Unauthorized. Approved enrollment required.'], 403);
            }
        }

        // Generate signed URL with 20 minutes expiry
        $signedUrl = $this->storageManager->driver()->generateSignedUrl($lecture->file_path, 20);

        return response()->json([
            'video_url' => $signedUrl
        ]);
    }

    /**
     * Remove the specified lecture from storage.
     */
    public function destroy(Lecture $lecture)
    {
        $user = Auth::user();
        $module = $lecture->module;
        $subject = $module->subject;

        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized.');
        }

        // Delete file from storage
        $this->storageManager->driver()->delete($lecture->file_path);

        $lecture->delete();

        return redirect()->back()->with('success', 'Lecture deleted successfully.');
    }
}
