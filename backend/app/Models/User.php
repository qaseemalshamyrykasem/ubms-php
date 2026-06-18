<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name', 'name_ar', 'email', 'phone', 'national_id', 'avatar',
        'password', 'telegram_chat_id', 'telegram_username',
        'telegram_verified', 'telegram_connected_at', 'telegram_verification_code',
        'status', 'locale', 'dark_mode', 'timezone', 'email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'telegram_verification_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'telegram_verified' => 'boolean',
            'telegram_connected_at' => 'datetime',
            'dark_mode' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ---- Relationships ----

    public function superAdminProfile(): HasOne
    {
        return $this->hasOne(SuperAdmin::class);
    }

    public function collegeAdminProfile(): HasOne
    {
        return $this->hasOne(CollegeAdmin::class);
    }

    public function representativeProfile(): HasOne
    {
        return $this->hasOne(Representative::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function siteNotifications(): HasMany
    {
        return $this->hasMany(SiteNotification::class);
    }

    public function unreadSiteNotifications()
    {
        return $this->siteNotifications()->where('is_read', false);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ---- Helpers ----

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isCollegeAdmin(): bool
    {
        return $this->hasRole('college_admin');
    }

    public function isRepresentative(): bool
    {
        return $this->hasRole('representative');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function primaryRole(): ?string
    {
        return $this->roles()->first()?->name;
    }

    public function telegramConnected(): bool
    {
        return (bool) $this->telegram_chat_id && $this->telegram_verified;
    }

    public function assignBatch($batchId): void
    {
        // Used by student registration
    }
}
