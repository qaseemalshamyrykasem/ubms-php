<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\DownloadLog;
use App\Models\SiteNotification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AssignmentService
{
    public function create(Batch $batch, User $user, array $data, array $files = []): Assignment
    {
        $data['batch_id'] = $batch->id;
        $data['user_id'] = $user->id;
        $data['notify_telegram'] = $data['notify_telegram'] ?? true;

        $assignment = Assignment::create($data);

        foreach ($files as $file) {
            $this->attachFile($assignment, $file);
        }

        // Notify students
        $this->notifyStudents($batch, $assignment);

        if ($assignment->notify_telegram) {
            app(TelegramService::class)->broadcastAssignment($assignment);
            $assignment->update(['telegram_notified' => true]);
        }

        AuditLog::record('assignment.create', $assignment);
        return $assignment->fresh('attachments');
    }

    public function update(Assignment $assignment, array $data, array $files = []): Assignment
    {
        $old = $assignment->toArray();
        $assignment->update($data);

        foreach ($files as $file) {
            $this->attachFile($assignment, $file);
        }

        AuditLog::record('assignment.update', $assignment, $old, $assignment->toArray());
        return $assignment->fresh('attachments');
    }

    public function delete(Assignment $assignment): void
    {
        foreach ($assignment->attachments as $att) {
            Storage::disk('public')->delete($att->file_path);
            $att->delete();
        }
        $assignment->delete();
        AuditLog::record('assignment.delete', $assignment);
    }

    public function recordDownload(Assignment $assignment, User $user, ?int $attachmentId = null): void
    {
        $attachment = $attachmentId
            ? $assignment->attachments()->find($attachmentId)
            : $assignment->attachments()->first();

        if (!$attachment) {
            return;
        }

        \App\Models\AssignmentDownload::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'attachment_id' => $attachment->id,
        ]);

        DownloadLog::create([
            'user_id' => $user->id,
            'file_path' => $attachment->file_path,
            'original_name' => $attachment->original_name,
            'resource_type' => Assignment::class,
            'resource_id' => $assignment->id,
            'ip_address' => request()?->ip(),
        ]);
    }

    protected function attachFile(Assignment $assignment, UploadedFile $file): void
    {
        $path = $file->store("assignments/{$assignment->id}", 'public');
        AssignmentAttachment::create([
            'assignment_id' => $assignment->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);
    }

    protected function notifyStudents(Batch $batch, Assignment $assignment): void
    {
        $studentIds = Student::where('batch_id', $batch->id)
            ->where('status', 'active')
            ->pluck('user_id');

        $rows = $studentIds->map(fn ($uid) => [
            'user_id' => $uid,
            'title' => __('assignments.posted', [], 'ar'),
            'body' => mb_substr($assignment->title, 0, 250),
            'type' => 'info',
            'link' => "/assignments/{$assignment->id}",
            'related_type' => Assignment::class,
            'related_id' => $assignment->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        foreach (array_chunk($rows, 500) as $chunk) {
            SiteNotification::insert($chunk);
        }
    }
}
