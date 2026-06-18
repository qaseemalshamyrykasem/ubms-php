<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;
use App\Exports\StudentsExport;
use App\Exports\AnnouncementsExport;
use App\Exports\AssignmentsExport;
use App\Exports\StatisticsExport;

class ReportService
{
    public function exportAttendanceExcel(Batch $batch, array $filters = [])
    {
        $filename = "attendance_{$batch->code}_" . now()->format('Ymd_His') . '.xlsx';
        AuditLog::record('report.export', $batch, null, ['type' => 'attendance_excel']);
        return Excel::download(new AttendanceExport($batch, $filters), $filename);
    }

    public function exportAttendancePdf(Batch $batch, array $filters = [])
    {
        $lectureIds = Lecture::where('batch_id', $batch->id)->pluck('id');
        $attendances = Attendance::with(['student', 'lecture.course'])
            ->whereIn('lecture_id', $lectureIds)
            ->when($filters['course_id'] ?? null, fn($q, $cid) => $q->whereHas('lecture', fn($qq) => $qq->where('course_id', $cid)))
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereHas('lecture', fn($qq) => $qq->where('date', '>=', $from)))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereHas('lecture', fn($qq) => $qq->where('date', '<=', $to)))
            ->get();

        $pdf = Pdf::loadView('reports.attendance', [
            'batch' => $batch,
            'attendances' => $attendances,
            'university' => $batch?->section?->level?->department?->college?->university,
            'generatedAt' => now(),
        ])->setPaper('a4', 'portrait');

        AuditLog::record('report.export', $batch, null, ['type' => 'attendance_pdf']);
        return $pdf->download("attendance_{$batch->code}.pdf");
    }

    public function exportStudentsExcel(Batch $batch)
    {
        $filename = "students_{$batch->code}_" . now()->format('Ymd_His') . '.xlsx';
        AuditLog::record('report.export', $batch, null, ['type' => 'students_excel']);
        return Excel::download(new StudentsExport($batch), $filename);
    }

    public function exportAnnouncementsExcel(Batch $batch)
    {
        $filename = "announcements_{$batch->code}_" . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AnnouncementsExport($batch), $filename);
    }

    public function exportAssignmentsExcel(Batch $batch)
    {
        $filename = "assignments_{$batch->code}_" . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new AssignmentsExport($batch), $filename);
    }

    public function exportStatisticsExcel(Batch $batch)
    {
        $filename = "statistics_{$batch->code}_" . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new StatisticsExport($batch), $filename);
    }

    public function generateStats(Batch $batch): array
    {
        $students = $batch->students()->where('status', 'active')->count();
        $announcements = $batch->announcements()->published()->count();
        $assignments = $batch->assignments()->count();
        $lectures = $batch->lectures()->count();

        $attendanceStats = app(AttendanceService::class)->batchStats($batch->id);

        // Daily activity (last 30 days)
        $dailyActivity = AuditLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereHasMorph('resource', [Announcement::class, Assignment::class, Attendance::class])
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Announcements by type
        $byType = $batch->announcements()
            ->published()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Downloads in last 30 days
        $downloads = \App\Models\DownloadLog::whereHasMorph('resource', [Assignment::class])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'students' => $students,
            'announcements' => $announcements,
            'assignments' => $assignments,
            'lectures' => $lectures,
            'attendance' => $attendanceStats,
            'daily_activity' => $dailyActivity,
            'announcements_by_type' => $byType,
            'downloads' => $downloads,
        ];
    }
}
