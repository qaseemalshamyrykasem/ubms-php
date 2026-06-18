<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Announcement\StoreRequest;
use App\Http\Requests\Announcement\UpdateRequest;
use App\Models\Announcement;
use App\Models\Batch;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(private AnnouncementService $service)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $batch = $this->resolveBatch($user, $request);

        if (!$batch) {
            return response()->json(['message' => __('batch.required')], 422);
        }

        $result = $this->service->search(
            $batch,
            $request->input('q', ''),
            $request->only(['type', 'course_id', 'pinned', 'per_page'])
        );

        return response()->json($result);
    }

    public function show(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        $user = request()->user();
        if ($user->isStudent()) {
            $this->service->markRead($announcement, $user);
        }
        $announcement->load('attachments', 'author', 'course', 'reads');
        return response()->json([
            'announcement' => $announcement,
            'stats' => $this->service->readStats($announcement),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', Announcement::class);
        $user = $request->user();
        $batch = $this->resolveBatch($user, $request);

        $announcement = $this->service->create(
            $batch,
            $user,
            $request->validated(),
            $request->file('attachments', [])
        );

        return response()->json(['announcement' => $announcement, 'message' => __('announcements.created')], 201);
    }

    public function update(UpdateRequest $request, Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $announcement = $this->service->update(
            $announcement,
            $request->validated(),
            $request->file('attachments', [])
        );
        return response()->json(['announcement' => $announcement, 'message' => __('announcements.updated')]);
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('delete', $announcement);
        $this->service->delete($announcement);
        return response()->json(['message' => __('announcements.deleted')]);
    }

    public function togglePin(Announcement $announcement)
    {
        $this->authorize('update', $announcement);
        $announcement = $this->service->togglePin($announcement);
        return response()->json(['announcement' => $announcement]);
    }

    public function stats(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        return response()->json($this->service->readStats($announcement));
    }

    private function resolveBatch($user, Request $request): ?Batch
    {
        // Rep uses their assigned batch; admin can pass batch_id
        if ($user->isRepresentative()) {
            return $user->representativeProfile?->batch;
        }
        if ($user->isStudent()) {
            return $user->studentProfile?->batch;
        }
        if ($user->isCollegeAdmin() || $user->isSuperAdmin()) {
            $id = $request->input('batch_id');
            return $id ? Batch::find($id) : null;
        }
        return null;
    }
}
