<?php

namespace App\Http\Controllers;

use App\Models\Lecture;
use App\Models\Module;
use App\Services\GoogleCloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    protected $gcpService;

    public function __construct(GoogleCloudStorageService $gcpService)
    {
        $this->gcpService = $gcpService;
    }

    /**
     * Store a newly created lecture in storage (upload video).
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'video' => 'required|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-la-asf,video/x-ms-asf,video/x-ms-wmv,video/x-msvideo,video/x-sgi-movie|max:102400', // max 100MB
        ]);

        $module = Module::findOrFail($request->module_id);
        $course = $module->course;
        $user = Auth::user();

        // Check if teacher is the creator of the course, or is admin
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($request->hasFile('video')) {
            // Upload path: /videos/course-id/
            $destinationPath = "videos/{$course->id}";
            $uploadedFilePath = $this->gcpService->upload($request->file('video'), $destinationPath);

            Lecture::create([
                'module_id' => $module->id,
                'title' => $request->title,
                'file_path' => $uploadedFilePath,
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
        $course = $module->course;

        // Check access: Admin or course teacher or enrolled student
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to watch this lecture.');
            }
        }

        return view('lectures.show', compact('lecture', 'course'));
    }

    /**
     * Get the signed URL for the video (JSON response).
     */
    public function getStreamUrl(Lecture $lecture)
    {
        $user = Auth::user();
        $module = $lecture->module;
        $course = $module->course;

        // Check access: Admin or course teacher or enrolled student
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
            if (!$isEnrolled) {
                return response()->json(['error' => 'Unauthorized. Enrollment required.'], 403);
            }
        }

        // Generate signed URL with 20 minutes expiry
        $signedUrl = $this->gcpService->generateSignedUrl($lecture->file_path, 20);

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
        $course = $module->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        // Delete file from storage
        $this->gcpService->delete($lecture->file_path);

        $lecture->delete();

        return redirect()->back()->with('success', 'Lecture deleted successfully.');
    }
}
