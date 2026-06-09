<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LiveClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveClassController extends Controller
{
    /**
     * Store a newly scheduled live class.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'datetime' => 'required|date|after:now',
            'link' => 'required|url',
        ]);

        $course = Course::findOrFail($request->course_id);
        $user = Auth::user();

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        LiveClass::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'datetime' => $request->datetime,
            'link' => $request->link,
        ]);

        return redirect()->back()->with('success', 'Live class scheduled successfully.');
    }

    /**
     * Update the specified live class in storage.
     */
    public function update(Request $request, LiveClass $liveClass)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'datetime' => 'required|date|after:now',
            'link' => 'required|url',
        ]);

        $user = Auth::user();
        $course = $liveClass->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $liveClass->update([
            'title' => $request->title,
            'datetime' => $request->datetime,
            'link' => $request->link,
        ]);

        return redirect()->back()->with('success', 'Live class updated successfully.');
    }

    /**
     * Remove the specified live class from storage.
     */
    public function destroy(LiveClass $liveClass)
    {
        $user = Auth::user();
        $course = $liveClass->course;

        if (!$user->isAdmin() && $course->teacher_id !== $user->id) {
            abort(403, 'Unauthorized.');
        }

        $liveClass->delete();

        return redirect()->back()->with('success', 'Live class deleted successfully.');
    }
}
