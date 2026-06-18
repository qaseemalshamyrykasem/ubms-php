<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceService
{
    public function createLecture(array $data, User $creator): Lecture
    {
        $data['created_by'] = $creator->id;
        $data['qr_token'] = Str::uuid()->toString();
        $data['qr_expires_at'] = now()->addMinutes(15);

        $lecture = Lecture::create($data);
        AuditLog::record('lecture.create', $lecture);
        return $lecture;
    }

    public function refreshQr(Lecture $lecture, int $ttlMinutes = 15): Lecture
    {
        $lecture->refreshQrToken($ttlMinutes);
        return $lecture;
    }

    public function generateQrSvg(Lecture $lecture, int $size = 300): string
    {
        $payload = json_encode([
            'lecture_id' => $lecture->id,
            'token' => $lecture->qr_token,
            'exp' => $lecture->qr_expires_at?->timestamp,
        ]);

        // simple-qrcode ^4.0: generate() returns SVG string directly
        return QrCode::size($size)
            ->margin(2)
            ->generate($payload);
    }

    public function submitManualAttendance(Lecture $lecture, array $records, User $recorder): void
    {
        if ($lecture->attendance_locked) {
            throw new \DomainException('attendance.locked');
        }

        DB::transaction(function () use ($lecture, $records, $recorder) {
            foreach ($records as $r) {
                $student = Student::where('user_id', $r['student_id'])->where('batch_id', $lecture->batch_id)->first();
                if (!$student) continue;

                Attendance::updateOrCreate(
                    ['lecture_id' => $lecture->id, 'student_id' => $r['student_id']],
                    [
                        'batch_student_id' => $student->id,
                        'status' => $r['status'],
                        'verification_method' => 'manual',
                        'recorded_at' => now(),
                        'recorded_by' => $recorder->id,
                        'notes' => $r['notes'] ?? null,
                    ]
                );
            }
        });

        AuditLog::record('attendance.submit', $lecture);
    }

    public function scanQr(Lecture $lecture, User $student, string $token): Attendance
    {
        if ($lecture->qr_token !== $token) {
            throw new \DomainException('attendance.qr_invalid');
        }

        if ($lecture->qr_expires_at && $lecture->qr_expires_at->isPast()) {
            throw new \DomainException('attendance.qr_expired');
        }

        $existing = Attendance::where('lecture_id', $lecture->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->status === 'present') {
            throw new \DomainException('attendance.duplicate');
        }

        $isLate = now()->gt(Carbon::parse($lecture->date->format('Y-m-d') . ' ' . $lecture->start_time)->addMinutes(15));

        $batchStudent = Student::where('user_id', $student->id)->where('batch_id', $lecture->batch_id)->first();

        $attendance = Attendance::updateOrCreate(
            ['lecture_id' => $lecture->id, 'student_id' => $student->id],
            [
                'batch_student_id' => $batchStudent?->id,
                'status' => $isLate ? 'late' : 'present',
                'verification_method' => 'qr',
                'recorded_at' => now(),
                'recorded_by' => $student->id,
            ]
        );

        return $attendance;
    }

    public function lockLecture(Lecture $lecture): Lecture
    {
        $lecture->lock();
        AuditLog::record('attendance.lock', $lecture);
        return $lecture;
    }

    public function studentStats(User $student): array
    {
        $studentModel = $student->studentProfile;
        if (!$studentModel) return [];

        $attendances = $studentModel->attendances()->with('lecture.course')->get();

        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $excused = $attendances->where('status', 'excused')->count();

        $rate = $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0;

        $byCourse = $attendances->groupBy(fn ($a) => $a->lecture?->course_id)
            ->map(fn ($items) => [
                'course' => $items->first()?->lecture?->course?->name_ar,
                'total' => $items->count(),
                'present' => $items->where('status', 'present')->count() + $items->where('status', 'late')->count(),
                'absent' => $items->where('status', 'absent')->count(),
                'rate' => $items->count() > 0
                    ? round(((($items->where('status', 'present')->count() + $items->where('status', 'late')->count()) / $items->count()) * 100), 1)
                    : 0,
            ])->values();

        return [
            'total_lectures' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'rate' => $rate,
            'by_course' => $byCourse,
        ];
    }

    public function batchStats(int $batchId): array
    {
        $lectureIds = Lecture::where('batch_id', $batchId)->pluck('id');
        $total = Attendance::whereIn('lecture_id', $lectureIds)->count();
        $present = Attendance::whereIn('lecture_id', $lectureIds)->where('status', 'present')->count();
        $late = Attendance::whereIn('lecture_id', $lectureIds)->where('status', 'late')->count();
        $absent = Attendance::whereIn('lecture_id', $lectureIds)->where('status', 'absent')->count();
        $excused = Attendance::whereIn('lecture_id', $lectureIds)->where('status', 'excused')->count();

        return [
            'total_records' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'excused' => $excused,
            'rate' => $total > 0 ? round((($present + $late) / $total) * 100, 2) : 0,
            'lectures' => $lectureIds->count(),
        ];
    }

    public function batchStudentRates(int $batchId): array
    {
        return Student::where('batch_id', $batchId)
            ->with(['user:id,name,name_ar,email,avatar', 'attendances'])
            ->get()
            ->map(fn ($s) => [
                'student' => [
                    'id' => $s->user->id,
                    'name' => $s->user->name,
                    'name_ar' => $s->user->name_ar,
                    'email' => $s->user->email,
                    'avatar' => $s->user->avatar,
                    'student_id' => $s->student_id,
                ],
                'total' => $s->attendances->count(),
                'present' => $s->attendances->where('status', 'present')->count(),
                'late' => $s->attendances->where('status', 'late')->count(),
                'absent' => $s->attendances->where('status', 'absent')->count(),
                'excused' => $s->attendances->where('status', 'excused')->count(),
                'rate' => $s->attendanceRate(),
            ])->toArray();
    }
}
