<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $schedules = Schedule::with(['course', 'instructor', 'exceptions'])
            ->where('batch_id', $batch->id)
            ->orderByRaw("FIELD(day, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')")
            ->orderBy('start_time')
            ->get();

        $grouped = $schedules->groupBy('day');
        return response()->json(['data' => $grouped, 'all' => $schedules]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Schedule::class);
        $user = $request->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'day' => 'required|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
            'building' => 'nullable|string|max:100',
            'instructor_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'is_recurring' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        $data['batch_id'] = $batch->id;
        $schedule = Schedule::create($data);
        return response()->json(['schedule' => $schedule, 'message' => __('schedules.created')], 201);
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->authorize('update', $schedule);
        $data = $request->validate([
            'course_id' => 'sometimes|exists:courses,id',
            'day' => 'sometimes|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'room' => 'sometimes|nullable|string|max:100',
            'building' => 'sometimes|nullable|string|max:100',
            'instructor_name' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
            'is_recurring' => 'boolean',
            'effective_from' => 'sometimes|nullable|date',
            'effective_until' => 'sometimes|nullable|date|after:effective_from',
        ]);

        $schedule->update($data);
        return response()->json(['schedule' => $schedule, 'message' => __('schedules.updated')]);
    }

    public function destroy(Schedule $schedule)
    {
        $this->authorize('delete', $schedule);
        $schedule->delete();
        return response()->json(['message' => __('schedules.deleted')]);
    }
}
