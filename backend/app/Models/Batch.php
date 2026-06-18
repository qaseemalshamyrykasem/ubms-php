<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'name', 'name_ar', 'code',
        'start_year', 'end_year', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_year' => 'integer',
        'end_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function representatives(): HasMany
    {
        return $this->hasMany(Representative::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'batch_course')
            ->withPivot(['level_id', 'semester'])
            ->withTimestamps();
    }

    public function getFullLabelAttribute(): string
    {
        return "{$this->code} - {$this->name_ar}";
    }

    public function chainPath(): string
    {
        $sec = $this->section;
        $lvl = $sec?->level;
        $dep = $lvl?->department;
        $col = $dep?->college;
        $uni = $col?->university;
        return collect([
            $uni?->name_ar, $col?->name_ar, $dep?->name_ar,
            $lvl?->name_ar, $sec?->name_ar, $this->name_ar,
        ])->filter()->implode(' / ');
    }
}
