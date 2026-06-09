<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    /**
     * Store a newly created module in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        Module::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
        ]);

        return redirect()->back()->with('success', 'Module created successfully.');
    }

    /**
     * Update the specified module in storage.
     */
    public function update(Request $request, Module $module)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $course = $module->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $module->update([
            'title' => $request->title,
        ]);

        return redirect()->back()->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module from storage.
     */
    public function destroy(Module $module)
    {
        $user = Auth::user();
        $course = $module->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $module->delete();

        return redirect()->back()->with('success', 'Module deleted successfully.');
    }
}
