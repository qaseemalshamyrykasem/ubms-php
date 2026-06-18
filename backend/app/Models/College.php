<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id', 'name', 'name_ar', 'code', 'dean_name', 'logo', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(CollegeAdmin::class);
    }
}
