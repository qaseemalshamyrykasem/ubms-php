<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service)
    {
    }

    public function lectures(Request $request)
    {
        $user = $request->user();
        $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $lectures = Lecture::with(['course', 'attendances'])
            ->where('batch_id', $batch->id)
            ->when($request->course_id, fn($q, $cid) => $q->where('course_id', $cid))
            ->when($request->from, fn($q, $from) => $q->where('date', '>=', $from))
            ->when($request->to, fn($q, $to) => $q->where('date', '<=', $to))
            ->orderByDesc('date')
            ->paginate($request->per_page ?? 15);

        return response()->json($lectures);
    }

    public function createLecture(Request $request)
    {
        $this->authorize('create', Lecture::class);
        $user = $request->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'nullable|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'room' => 'nullable|string|max:100',
        ]);

        $data['batch_id'] = $batch->id;
        $lecture = $this->service->createLecture($data, $user);
        return response()->json(['lecture' => $lecture, 'message' => __('attendance.lecture_created')], 201);
    }

    public function refreshQr(Lecture $lecture)
    {
        $this->authorize('update', $lecture);
        $lecture = $this->service->refreshQr($lecture);
        return response()->json(['lecture' => $lecture]);
    }

    public function qrCode(Lecture $lecture)
    {
        $this->authorize('view', $lecture);
        $svg = $this->service->generateQrSvg($lecture);
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }

    public function submit(Request $request, Lecture $lecture)
    {
        $this->authorize('update', $lecture);
        $data = $request->validate([
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:users,id',
            'records.*.status' => 'required|in:present,absent,late,excused',
            'records.*.notes' => 'nullable|string|max:255',
        ]);

        $this->service->submitManualAttendance($lecture, $data['records'], $request->user());
        return response()->json(['message' => __('attendance.submitted')]);
    }

    public function scan(Request $request)
    {
        $data = $request->validate([
            'lecture_id' => 'required|exists:lectures,id',
            'token' => 'required|string',
        ]);

        $lecture = Lecture::findOrFail($data['lecture_id']);
        $attendance = $this->service->scanQr($lecture, $request->user(), $data['token']);
        return response()->json(['attendance' => $attendance, 'message' => __('attendance.recorded')]);
    }

    public function lock(Lecture $lecture)
    {
        $this->authorize('update', $lecture);
        $this->service->lockLecture($lecture);
        return response()->json(['message' => __('attendance.locked')]);
    }

    public function myStats(Request $request)
    {
        return response()->json($this->service->studentStats($request->user()));
    }

    public function batchStats(Request $request)
    {
        $user = $request->user();
        $batchId = $request->input('batch_id');

        if ($user->isRepresentative()) {
            $batchId = $user->representativeProfile?->batch_id;
        } elseif ($user->isStudent()) {
            $batchId = $user->studentProfile?->batch_id;
        }

        if (!$batchId) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $stats = $this->service->batchStats($batchId);
        $studentRates = $this->service->batchStudentRates($batchId);
        return response()->json([
            'stats' => $stats,
            'students' => $studentRates,
        ]);
    }

    public function myHistory(Request $request)
    {
        $user = $request->user();
        $attendances = Attendance::with(['lecture.course'])
            ->where('student_id', $user->id)
            ->when($request->course_id, fn($q, $cid) => $q->whereHas('lecture', fn($qq) => $qq->where('course_id', $cid)))
            ->when($request->from, fn($q, $from) => $q->whereHas('lecture', fn($qq) => $qq->where('date', '>=', $from)))
            ->when($request->to, fn($q, $to) => $q->whereHas('lecture', fn($qq) => $qq->where('date', '<=', $to)))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 15);

        return response()->json($attendances);
    }
}
