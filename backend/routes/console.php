<?php

use App\Services\AnnouncementService;
use Illuminate\Support\Facades\Schedule;

// Publish scheduled announcements every minute
Schedule::call(function () {
    app(AnnouncementService::class)->publishScheduled();
})->everyMinute()->name('publish-scheduled-announcements')->withoutOverlapping();

// Clean old site notifications daily (older than 90 days)
Schedule::call(function () {
    \App\Models\SiteNotification::where('created_at', '<', now()->subDays(90))->delete();
})->daily()->at('03:00')->name('cleanup-old-notifications');

// Clean old audit logs weekly (older than 1 year)
Schedule::call(function () {
    \App\Models\AuditLog::where('created_at', '<', now()->subYear())->delete();
})->weekly()->sundays()->at('04:00')->name('cleanup-old-audit-logs');

// Retry failed Telegram messages every 5 minutes
Schedule::call(function () {
    $failed = \App\Models\TelegramMessage::where('status', 'failed')
        ->where('created_at', '>', now()->subHours(1))
        ->limit(50)
        ->get();
    $telegram = app(\App\Services\TelegramService::class);
    foreach ($failed as $msg) {
        $telegram->sendMessage($msg->chat_id, $msg->message, $msg->payload ?? []);
    }
})->everyFiveMinutes()->name('retry-failed-telegram')->withoutOverlapping();

// Clean expired QR tokens daily (set expired tokens to a fresh value so they cannot be reused)
Schedule::call(function () {
    \App\Models\Lecture::where('qr_expires_at', '<', now())
        ->whereNotNull('qr_expires_at')
        ->update([
            'qr_token' => \Illuminate\Support\Str::uuid()->toString(),
            'qr_expires_at' => null,
        ]);
})->daily()->at('02:00')->name('cleanup-expired-qr-tokens');
