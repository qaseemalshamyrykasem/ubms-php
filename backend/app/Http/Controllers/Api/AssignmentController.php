<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function __construct(private AssignmentService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $items = Assignment::with(['course', 'author', 'attachments'])
            ->where('batch_id', $batch->id)
            ->when($request->course_id, fn($q, $cid) => $q->where('course_id', $cid))
            ->when($request->q, function ($q, $q_) {
                $q->where('title', 'like', "%{$q_}%")
                  ->orWhere('description', 'like', "%{$q_}%");
            })
            ->orderByDesc('deadline')
            ->paginate($request->per_page ?? 15);

        return response()->json($items);
    }

    public function show(Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        $assignment->load('attachments', 'author', 'course');
        return response()->json(['assignment' => $assignment]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Assignment::class);
        $user = $request->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'course_id' => 'nullable|exists:courses,id',
            'deadline' => 'required|date|after:now',
            'max_grade' => 'integer|min:1|max:1000',
            'allow_late_submission' => 'boolean',
            'late_penalty_percent' => 'integer|min:0|max:100',
            'notify_telegram' => 'boolean',
            'attachments.*' => 'file|max:20480',
        ]);

        $assignment = $this->service->create($batch, $user, $data, $request->file('attachments', []));
        return response()->json(['assignment' => $assignment, 'message' => __('assignments.created')], 201);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorize('update', $assignment);
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:10000',
            'course_id' => 'nullable|exists:courses,id',
            'deadline' => 'sometimes|date',
            'max_grade' => 'integer|min:1|max:1000',
            'allow_late_submission' => 'boolean',
            'late_penalty_percent' => 'integer|min:0|max:100',
            'attachments.*' => 'file|max:20480',
        ]);

        $assignment = $this->service->update($assignment, $data, $request->file('attachments', []));
        return response()->json(['assignment' => $assignment, 'message' => __('assignments.updated')]);
    }

    public function destroy(Assignment $assignment)
    {
        $this->authorize('delete', $assignment);
        $this->service->delete($assignment);
        return response()->json(['message' => __('assignments.deleted')]);
    }

    public function downloadAttachment(Request $request, Assignment $assignment, int $attachmentId)
    {
        $this->authorize('view', $assignment);
        $att = $assignment->attachments()->findOrFail($attachmentId);
        $this->service->recordDownload($assignment, $request->user(), $att->id);

        if (!Storage::disk('public')->exists($att->file_path)) {
            return response()->json(['message' => __('file.not_found')], 404);
        }

        return Storage::disk('public')->download($att->file_path, $att->original_name);
    }
}
