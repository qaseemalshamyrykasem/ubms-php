<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SiteNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'body', 'type', 'link',
        'related_type', 'related_id', 'is_read', 'read_at', 'scheduled_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeUnread($q)
    {
        return $q->where('is_read', false);
    }
}
