<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\DownloadLog;
use App\Models\Lecture;
use App\Models\Student;
use App\Services\AttendanceService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request, AttendanceService $attendanceService)
    {
        $user = $request->user();

        if ($user->isStudent()) {
            return $this->studentStats($user, $attendanceService);
        }
        if ($user->isRepresentative()) {
            return $this->repStats($user, $attendanceService);
        }
        if ($user->isCollegeAdmin()) {
            return $this->collegeAdminStats($user);
        }
        if ($user->isSuperAdmin()) {
            return $this->superAdminStats();
        }
        return response()->json([]);
    }

    private function studentStats($user, AttendanceService $service)
    {
        $batch = $user->studentProfile?->batch;
        if (!$batch) return response()->json(['message' => __('batch.required')], 422);

        $announcements = $batch->announcements()->published()->count();
        $assignments = $batch->assignments()->count();
        $attendance = $service->studentStats($user);

        $unread = $user->unreadNotifications()->count();

        $upcoming = Assignment::where('batch_id', $batch->id)
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        $recentAnnouncements = $batch->announcements()
            ->published()
            ->with('author', 'course')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return response()->json([
            'role' => 'student',
            'batch' => ['id' => $batch->id, 'code' => $batch->code, 'name_ar' => $batch->name_ar],
            'stats' => [
                'announcements' => $announcements,
                'assignments' => $assignments,
                'unread_notifications' => $unread,
                'attendance_rate' => $attendance['rate'] ?? 0,
                'present' => $attendance['present'] ?? 0,
                'absent' => $attendance['absent'] ?? 0,
                'late' => $attendance['late'] ?? 0,
            ],
            'upcoming_assignments' => $upcoming,
            'recent_announcements' => $recentAnnouncements,
            'attendance_stats' => $attendance,
        ]);
    }

    private function repStats($user, AttendanceService $service)
    {
        $batch = $user->representativeProfile?->batch;
        if (!$batch) return response()->json(['message' => __('batch.required')], 422);

        $students = $batch->students()->where('status', 'active')->count();
        $announcements = $batch->announcements()->count();
        $assignments = $batch->assignments()->count();
        $lectures = $batch->lectures()->count();
        $attendanceStats = $service->batchStats($batch->id);

        // Daily activity last 14 days
        $dailyActivity = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereDate('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Announcements by type
        $byType = $batch->announcements()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Recent activity (audit log)
        $recentActivity = AuditLog::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'role' => 'representative',
            'batch' => [
                'id' => $batch->id,
                'code' => $batch->code,
                'name_ar' => $batch->name_ar,
                'chain' => $batch->chainPath(),
            ],
            'stats' => [
                'students' => $students,
                'announcements' => $announcements,
                'assignments' => $assignments,
                'lectures' => $lectures,
                'attendance_rate' => $attendanceStats['rate'],
                'attendance' => $attendanceStats,
            ],
            'daily_activity' => $dailyActivity,
            'announcements_by_type' => $byType,
            'recent_activity' => $recentActivity,
        ]);
    }

    private function collegeAdminStats($user)
    {
        $collegeId = $user->collegeAdminProfile?->college_id;
        $college = $user->collegeAdminProfile?->college;

        $batchesCount = Batch::whereHas('section.level.department', fn($q) => $q->where('college_id', $collegeId))->count();
        $studentsCount = Student::whereHas('batch.section.level.department', fn($q) => $q->where('college_id', $collegeId))->count();
        $departmentsCount = $college?->departments()->count() ?? 0;

        return response()->json([
            'role' => 'college_admin',
            'college' => ['id' => $college?->id, 'name_ar' => $college?->name_ar],
            'stats' => [
                'batches' => $batchesCount,
                'students' => $studentsCount,
                'departments' => $departmentsCount,
            ],
        ]);
    }

    private function superAdminStats()
    {
        return response()->json([
            'role' => 'super_admin',
            'stats' => [
                'universities' => \App\Models\University::count(),
                'colleges' => \App\Models\College::count(),
                'batches' => Batch::count(),
                'students' => Student::count(),
                'users' => \App\Models\User::count(),
                'announcements' => Announcement::count(),
                'assignments' => Assignment::count(),
                'lectures' => Lecture::count(),
            ],
        ]);
    }

    public function globalSearch(Request $request)
    {
        $q = $request->input('q', '');
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = [];

        if ($user->isStudent() || $user->isRepresentative()) {
            $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
            if ($batch) {
                $results['announcements'] = $batch->announcements()
                    ->published()
                    ->where('title', 'like', "%{$q}%")
                    ->limit(10)
                    ->get(['id', 'title', 'type', 'published_at']);
                $results['assignments'] = $batch->assignments()
                    ->where('title', 'like', "%{$q}%")
                    ->limit(10)
                    ->get(['id', 'title', 'deadline']);
            }
        }

        if ($user->isCollegeAdmin() || $user->isSuperAdmin()) {
            $results['students'] = Student::whereHas('user', fn($qq) => $qq->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                ->with('user', 'batch')
                ->limit(10)
                ->get();
            $results['batches'] = Batch::where('code', 'like', "%{$q}%")
                ->orWhere('name_ar', 'like', "%{$q}%")
                ->limit(10)
                ->get(['id', 'code', 'name_ar']);
        }

        return response()->json(['results' => $results]);
    }
}
