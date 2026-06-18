<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['level_id', 'name', 'name_ar', 'code', 'capacity', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'capacity' => 'integer'];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
