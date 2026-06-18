<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecture_id', 'student_id', 'batch_student_id', 'status',
        'verification_method', 'recorded_at', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function batchStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'batch_student_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function excuses(): HasMany
    {
        return $this->hasMany(AttendanceExcuse::class);
    }

    public function scopePresent($q) { return $q->where('status', 'present'); }
    public function scopeAbsent($q)  { return $q->where('status', 'absent'); }
    public function scopeLate($q)    { return $q->where('status', 'late'); }
    public function scopeExcused($q) { return $q->where('status', 'excused'); }
}
