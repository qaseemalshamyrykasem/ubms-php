<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 'course_id', 'title', 'date', 'start_time', 'end_time',
        'room', 'qr_token', 'qr_expires_at', 'attendance_locked', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'qr_expires_at' => 'datetime',
        'attendance_locked' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function qrIsValid(): bool
    {
        return !$this->qr_expires_at || $this->qr_expires_at->isFuture();
    }

    public function refreshQrToken(int $ttlMinutes = 15): self
    {
        $this->qr_token = \Str::uuid()->toString();
        $this->qr_expires_at = now()->addMinutes($ttlMinutes);
        $this->save();
        return $this;
    }

    public function lock(): self
    {
        $this->attendance_locked = true;
        $this->save();
        return $this;
    }
}
