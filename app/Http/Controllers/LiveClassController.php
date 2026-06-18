<?php

namespace App\Http\Controllers;

use App\Models\Subject;
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
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'datetime' => 'required|date|after:now',
            'link' => 'required|url',
            'duration_minutes' => 'required|integer|min:5|max:480',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        $user = Auth::user();

        // Check if teacher is assigned to this subject
        if (!$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to schedule live classes.');
        }

        LiveClass::create([
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'datetime' => $request->datetime,
            'link' => $request->link,
            'duration_minutes' => $request->duration_minutes,
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
            'duration_minutes' => 'required|integer|min:5|max:480',
        ]);

        $user = Auth::user();
        $subject = $liveClass->subject;

        if (!$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to manage live classes.');
        }

        $liveClass->update([
            'title' => $request->title,
            'datetime' => $request->datetime,
            'link' => $request->link,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return redirect()->back()->with('success', 'Live class updated successfully.');
    }

    /**
     * Remove the specified live class from storage.
     */
    public function destroy(LiveClass $liveClass)
    {
        $user = Auth::user();
        $subject = $liveClass->subject;

        if (!$user->isAssignedToSubject($subject)) {
            abort(403, 'Unauthorized. You must be assigned to this subject to cancel live classes.');
        }

        $liveClass->delete();

        return redirect()->back()->with('success', 'Live class deleted successfully.');
    }

    /**
     * Join a live class, record attendance, and redirect to the class link.
     */
    public function join(LiveClass $liveClass)
    {
        $user = Auth::user();
        $subject = $liveClass->subject;
        $course = $subject->course;

        // Check access
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
                abort(403, 'You must have an approved enrollment in this course to join this live class.');
            }
        }

        if ($user->isStudent()) {
            \App\Models\LiveClassAttendance::firstOrCreate([
                'user_id' => $user->id,
                'live_class_id' => $liveClass->id,
            ], [
                'attended_at' => now(),
            ]);

            \App\Models\UsageLog::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'action' => 'join_live_class',
                'details' => "Joined live class: {$liveClass->title}",
            ]);
        }

        return redirect()->away($liveClass->link);
    }
}
