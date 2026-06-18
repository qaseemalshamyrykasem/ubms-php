<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 'course_id', 'day', 'start_time', 'end_time',
        'room', 'building', 'instructor_id', 'instructor_name',
        'notes', 'is_recurring', 'effective_from', 'effective_until',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    public const DAYS = [
        'sunday', 'monday', 'tuesday', 'wednesday',
        'thursday', 'friday', 'saturday',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ScheduleException::class);
    }
}
