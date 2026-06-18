<?php

namespace App\Services\Native;

/**
 * NotificationService
 *
 * Handles native push notifications for the mobile app.
 * Uses Capacitor LocalNotifications plugin via JavaScript bridge.
 *
 * On Android, also handles badge counts and vibration patterns.
 */
class NotificationService
{
    /**
     * Initialize the notification service.
     * Called once on app boot (when native runtime detected).
     */
    public function initialize(): void
    {
        // No-op on PHP side - actual permission requests happen via JS bridge.
        // This method exists for future expansion (e.g., registering push tokens).
    }

    /**
     * Schedule a local notification.
     *
     * @param string $title   Notification title
     * @param string $body    Notification body
     * @param array  $options Additional options (schedule, sound, etc.)
     */
    public function notify(string $title, string $body, array $options = []): void
    {
        // Notifications are dispatched via the JavaScript bridge.
        // The PHP side logs them for audit; the JS side triggers the actual
        // Capacitor LocalNotifications call.
        logger()->info('Native notification queued', [
            'title' => $title,
            'body' => $body,
            'options' => $options,
        ]);
    }

    /**
     * Get the unread notification count for the user.
     * Used by the mobile app to display the badge count.
     */
    public function getUnreadCount(): int
    {
        $user = auth()->user();
        if (!$user) {
            return 0;
        }

        return \App\Models\SiteNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Trigger device vibration.
     * Mobile-only; no-op on web.
     *
     * @param int $milliseconds Duration in ms
     */
    public function vibrate(int $milliseconds = 100): void
    {
        // Vibration is handled by the JS bridge (Capacitor Haptics plugin).
        // PHP-side logging only.
        logger()->debug('Vibration requested', ['ms' => $milliseconds]);
    }
}
