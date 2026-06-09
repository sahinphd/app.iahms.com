<?php

namespace App\Http\Controllers;

use App\Models\ClassNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassNoteController extends Controller
{
    /**
     * Store a newly created class note in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|string',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $user = Auth::user();
        $classId = $request->school_class_id;

        if ($classId === 'all') {
            // Global notice: must be admin or teacher with at least one class assignment
            if (!$user->isAdmin() && !$user->assignedClasses()->exists()) {
                return redirect()->back()->with('error', 'You must be assigned to at least one class to post a global notice.');
            }
            $targetClassId = null;
        } else {
            // Specific class validation
            $request->validate([
                'school_class_id' => 'exists:school_classes,id',
            ]);
            $class = \App\Models\SchoolClass::findOrFail($classId);
            if (!$user->isAssignedToClass($class)) {
                return redirect()->back()->with('error', 'You are not assigned to manage this class noticeboard.');
            }
            $targetClassId = $classId;
        }

        ClassNote::create([
            'teacher_id' => $user->id,
            'school_class_id' => $targetClassId,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->back()->with('success', 'Class note posted successfully!');
    }

    /**
     * Remove the specified class note from database.
     */
    public function destroy(ClassNote $classNote)
    {
        $user = Auth::user();

        // Check if user is admin or is the author of the note
        if (!$user->isAdmin() && $classNote->teacher_id !== $user->id) {
            abort(403, 'Unauthorized action. You did not author this note.');
        }

        $classNote->delete();

        return redirect()->back()->with('success', 'Class note removed successfully.');
    }
}
