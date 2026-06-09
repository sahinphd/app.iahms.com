<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Module;
use App\Services\GoogleCloudStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    protected $gcpService;

    public function __construct(GoogleCloudStorageService $gcpService)
    {
        $this->gcpService = $gcpService;
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
        $course = $module->course;
        $user = Auth::user();

        // Check auth: creator teacher or admin
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        if ($request->hasFile('material_file')) {
            // Upload path: /materials/course-id/
            $destinationPath = "materials/{$course->id}";
            $uploadedFilePath = $this->gcpService->upload($request->file('material_file'), $destinationPath);

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
        $course = $module->course;

        // Check access: Admin or course teacher or enrolled student
        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            $isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
            if (!$isEnrolled) {
                abort(403, 'You must be enrolled in this course to download this material.');
            }
        }

        // Generate a signed URL for secure download (expires in 15 minutes)
        $signedUrl = $this->gcpService->generateSignedUrl($material->file_path, 15);

        return redirect()->away($signedUrl);
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Material $material)
    {
        $user = Auth::user();
        $module = $material->module;
        $course = $module->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        // Delete from storage
        $this->gcpService->delete($material->file_path);

        $material->delete();

        return redirect()->back()->with('success', 'Material deleted successfully.');
    }
}
