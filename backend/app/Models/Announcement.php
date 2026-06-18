<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_id', 'user_id', 'course_id', 'title', 'body', 'type',
        'is_pinned', 'is_published', 'scheduled_at', 'published_at', 'expires_at',
        'send_telegram', 'telegram_sent',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_published' => 'boolean',
        'send_telegram' => 'boolean',
        'telegram_sent' => 'boolean',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function readBy(User $user): bool
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    public function markReadBy(User $user): void
    {
        $this->reads()->firstOrCreate(
            ['user_id' => $user->id],
            ['read_at' => now()]
        );
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true)
                 ->where(function($qq){
                     $qq->whereNull('scheduled_at')
                        ->orWhere('scheduled_at', '<=', now());
                 })
                 ->where(function($qq){
                     $qq->whereNull('expires_at')
                        ->orWhere('expires_at', '>=', now());
                 });
    }

    public function scopePinned($q)
    {
        return $q->where('is_pinned', true);
    }
}
