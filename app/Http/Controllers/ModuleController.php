<?php

namespace App\Http\Controllers;

use App\Models\Subject;
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
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $user = Auth::user();

        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage its syllabus.');
        }

        Module::create([
            'subject_id' => $request->subject_id,
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
        $subject = $module->subject;

        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage its syllabus.');
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
        $subject = $module->subject;

        if (!$user->hasPermission('manage_syllabus') || !$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage its syllabus.');
        }

        $module->delete();

        return redirect()->back()->with('success', 'Module deleted successfully.');
    }
}

