<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'body', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
