<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_id', 'course_id', 'user_id', 'title', 'description', 'deadline',
        'max_grade', 'allow_late_submission', 'late_penalty_percent',
        'notify_telegram', 'telegram_notified',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'max_grade' => 'integer',
        'allow_late_submission' => 'boolean',
        'late_penalty_percent' => 'integer',
        'notify_telegram' => 'boolean',
        'telegram_notified' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(AssignmentDownload::class);
    }

    public function isOverdue(): bool
    {
        return $this->deadline->isPast();
    }
}
