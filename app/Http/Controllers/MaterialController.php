<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Module;
use App\Services\Storage\StorageManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    protected $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    /**
     * Store a newly created study material in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'title' => 'required|string|max:255',
            'material_file' => 'required|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:51200', // max 50MB
        ]);

        $module = Module::findOrFail($request->module_id);
        $subject = $module->subject;
        $course = $subject->course;
        $user = Auth::user();

        // Check access: user has permission and is assigned to this subject (or is admin)
        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage its syllabus.');
        }

        if ($request->hasFile('material_file')) {
            // Upload path: /materials/course-id/
            $destinationPath = "materials/{$course->id}";
            $uploadedFilePath = $this->storageManager->driver()->upload($request->file('material_file'), $destinationPath);

            Material::create([
                'module_id' => $module->id,
                'title' => $request->title,
                'file_path' => $uploadedFilePath,
            ]);

            return redirect()->back()->with('success', 'Study material uploaded successfully.');
        }

        return redirect()->back()->withErrors(['material_file' => 'Failed to upload file.']);
    }

    /**
     * Download study material securely.
     */
    public function download(Material $material)
    {
        $user = Auth::user();
        $module = $material->module;
        $subject = $module->subject;
        $course = $subject->course;

        // Check access: Admin or assigned subject teacher or enrolled student
        if (!$user->isAssignedToSubject($subject)) {
            $isEnrolled = false;
            if ($course->school_class_id && $course->school_class_id === $user->school_class_id) {
                $isEnrolled = true;
            } else {
                $isEnrolled = $user->enrolledCourses()
                    ->where('course_id', $course->id)
                    ->where('enrollments.is_approved', true)
                    ->exists();
            }
            if (!$isEnrolled) {
                abort(403, 'You must have an approved enrollment in this course to download this material.');
            }
        }

        if ($user->isStudent()) {
            \App\Models\UsageLog::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'action' => 'download_material',
                'details' => "Downloaded study material: {$material->title}",
            ]);
        }

        // If GCP/R2/Mux storage is configured, redirect to signed URL
        if (env('GCP_BUCKET') || \App\Models\Setting::get('active_storage_driver') !== 'local') {
            $signedUrl = $this->storageManager->driver()->generateSignedUrl($material->file_path, 15);
            return redirect()->away($signedUrl);
        }

        // Local fallback: Stream file directly from storage securely
        $fullPath = storage_path("app/public/{$material->file_path}");
        if (file_exists($fullPath)) {
            return response()->download($fullPath);
        }

        abort(404, 'Study material file not found.');
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Material $material)
    {
        $user = Auth::user();
        $module = $material->module;
        $subject = $module->subject;

        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized.');
        }

        // Delete from storage
        $this->storageManager->driver()->delete($material->file_path);

        $material->delete();

        return redirect()->back()->with('success', 'Material deleted successfully.');
    }
}
