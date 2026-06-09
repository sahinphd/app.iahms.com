<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\UsageLog;
use App\Models\User;
use App\Models\Course;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display usage reports and login histories.
     */
    public function index()
    {
        // Stats
        $stats = [
            'total_logins' => LoginHistory::count(),
            'total_activities' => UsageLog::count(),
            'video_watches' => UsageLog::where('action', 'watch_video')->count(),
            'material_downloads' => UsageLog::where('action', 'download_material')->count(),
        ];

        // Fetch login histories ordered by date
        $loginHistories = LoginHistory::with('user')
            ->orderBy('logged_in_at', 'desc')
            ->limit(500)
            ->get();

        // Fetch activity logs ordered by date
        $usageLogs = UsageLog::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        // Fetch all students with their class and enrolled courses progress/attendance details
        $students = User::where('role', 'student')
            ->with(['schoolClass', 'enrolledCourses.subjects.modules.lectures', 'enrolledCourses.subjects.liveClasses', 'lectureProgresses', 'liveClassAttendances'])
            ->get();

        $studentAnalytics = [];
        foreach ($students as $stu) {
            $totalLectures = 0;
            $completedLectures = 0;
            $totalWatchSeconds = 0;
            
            // Gather all lecture IDs and live class IDs for their enrolled courses
            $enrolledLectureIds = [];
            $enrolledLiveClassIds = [];
            
            foreach ($stu->enrolledCourses as $course) {
                foreach ($course->subjects as $subj) {
                    foreach ($subj->modules as $mod) {
                        foreach ($mod->lectures as $lec) {
                            $enrolledLectureIds[] = $lec->id;
                        }
                    }
                    foreach ($subj->liveClasses as $lc) {
                        $enrolledLiveClassIds[] = $lc->id;
                    }
                }
            }

            $enrolledLectureIds = array_unique($enrolledLectureIds);
            $enrolledLiveClassIds = array_unique($enrolledLiveClassIds);

            $totalLectures = count($enrolledLectureIds);
            
            // Filter user progress records that belong to these enrolled lectures
            foreach ($stu->lectureProgresses as $prog) {
                if (in_array($prog->lecture_id, $enrolledLectureIds)) {
                    $totalWatchSeconds += $prog->seconds_watched;
                    if ($prog->is_completed) {
                        $completedLectures++;
                    }
                }
            }

            $videoProgressPercent = $totalLectures > 0 ? round(($completedLectures / $totalLectures) * 100) : 0;
            $watchHours = round($totalWatchSeconds / 3600, 2);

            $totalLiveClasses = count($enrolledLiveClassIds);
            $attendedCount = 0;
            foreach ($stu->liveClassAttendances as $att) {
                if (in_array($att->live_class_id, $enrolledLiveClassIds)) {
                    $attendedCount++;
                }
            }
            $attendanceRate = $totalLiveClasses > 0 ? round(($attendedCount / $totalLiveClasses) * 100) : 0;

            $studentAnalytics[] = [
                'student' => $stu,
                'courses' => $stu->enrolledCourses->pluck('title')->toArray(),
                'progress_percent' => $videoProgressPercent,
                'watch_hours' => $watchHours,
                'attendance_rate' => $attendanceRate,
            ];
        }

        return view('admin.reports.index', compact('stats', 'loginHistories', 'usageLogs', 'studentAnalytics'));
    }
}
