<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\AnnouncementAttachment;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\SiteNotification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementService
{
    public function create(Batch $batch, User $user, array $data, array $files = []): Announcement
    {
        $data['batch_id'] = $batch->id;
        $data['user_id'] = $user->id;
        $data['is_published'] = $data['is_published'] ?? true;
        $data['published_at'] = $data['is_published'] && empty($data['scheduled_at']) ? now() : null;

        $announcement = Announcement::create($data);

        foreach ($files as $file) {
            $this->attachFile($announcement, $file);
        }

        // Notify students
        if ($announcement->is_published) {
            $this->notifyStudents($batch, $announcement);
        }

        if (!empty($data['send_telegram'])) {
            app(TelegramService::class)->broadcastAnnouncement($announcement);
        }

        AuditLog::record('announcement.create', $announcement);
        return $announcement->fresh('attachments');
    }

    public function update(Announcement $announcement, array $data, array $files = []): Announcement
    {
        $old = $announcement->toArray();
        $announcement->update($data);

        foreach ($files as $file) {
            $this->attachFile($announcement, $file);
        }

        AuditLog::record('announcement.update', $announcement, $old, $announcement->toArray());
        return $announcement->fresh('attachments');
    }

    public function delete(Announcement $announcement): void
    {
        foreach ($announcement->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        }
        $announcement->delete();
        AuditLog::record('announcement.delete', $announcement);
    }

    public function togglePin(Announcement $announcement): Announcement
    {
        $announcement->is_pinned = !$announcement->is_pinned;
        $announcement->save();
        return $announcement;
    }

    public function publishScheduled(): int
    {
        $count = 0;
        Announcement::where('is_published', false)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->chunk(100, function ($items) use (&$count) {
                foreach ($items as $a) {
                    $a->update([
                        'is_published' => true,
                        'published_at' => now(),
                    ]);
                    $this->notifyStudents($a->batch, $a);
                    if ($a->send_telegram) {
                        app(TelegramService::class)->broadcastAnnouncement($a);
                    }
                    $count++;
                }
            });
        return $count;
    }

    public function markRead(Announcement $announcement, User $user): void
    {
        $announcement->markReadBy($user);
    }

    public function readStats(Announcement $announcement): array
    {
        $total = $announcement->batch->students()->count();
        $read = $announcement->reads()->count();
        return [
            'total' => $total,
            'read' => $read,
            'unread' => max(0, $total - $read),
            'rate' => $total > 0 ? round(($read / $total) * 100, 1) : 0,
        ];
    }

    public function search(Batch $batch, string $q, array $filters = [])
    {
        $query = $batch->announcements()->published()->with(['author', 'course', 'attachments']);

        if ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('body', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }

        if (!empty($filters['pinned'])) {
            $query->where('is_pinned', true);
        }

        return $query->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate($filters['per_page'] ?? 15);
    }

    protected function attachFile(Announcement $announcement, UploadedFile $file): void
    {
        $path = $file->store("announcements/{$announcement->id}", 'public');
        AnnouncementAttachment::create([
            'announcement_id' => $announcement->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);
    }

    protected function notifyStudents(Batch $batch, Announcement $announcement): void
    {
        $studentIds = Student::where('batch_id', $batch->id)
            ->where('status', 'active')
            ->pluck('user_id');

        $rows = $studentIds->map(fn ($uid) => [
            'user_id' => $uid,
            'title' => mb_substr($announcement->title, 0, 100),
            'body' => mb_substr(strip_tags($announcement->body), 0, 250),
            'type' => $announcement->type,
            'link' => "/announcements/{$announcement->id}",
            'related_type' => Announcement::class,
            'related_id' => $announcement->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            SiteNotification::insert($chunk);
        }
    }
}
