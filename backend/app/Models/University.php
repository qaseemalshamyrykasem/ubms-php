<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'name_ar', 'code', 'logo', 'country', 'city', 'address',
        'phone', 'email', 'website', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function colleges(): HasMany
    {
        return $this->hasMany(College::class);
    }
}
