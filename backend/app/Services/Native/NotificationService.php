<?php

namespace App\Services\Native;

use NativePHP\Laravel\Facades\NativePHP;

class NotificationService
{
    public function initialize(): void
    {
        if (!NativePHP::isNative()) return;

        // Request notification permission on Android 13+
        if (PHP_OS_FAMILY === 'Linux' || $this->isAndroid()) {
            $this->requestPermission();
        }
    }

    public function notify(string $title, string $body, array $data = []): void
    {
        if (!NativePHP::isNative()) return;

        NativePHP::notify($title, $body, $data);
    }

    public function vibrate(int $milliseconds = 100): void
    {
        if (!NativePHP::isNative()) return;

        NativePHP::settings()->set('vibrate', $milliseconds);
    }

    public function playSound(string $type = 'default'): void
    {
        if (!NativePHP::isNative()) return;
        NativePHP::settings()->set('sound', $type);
    }

    private function isAndroid(): bool
    {
        return isset($_ENV['NATIVEPHP_PLATFORM']) && $_ENV['NATIVEPHP_PLATFORM'] === 'android';
    }

    private function requestPermission(): void
    {
        NativePHP::settings()->set('permission.notifications', true);
    }
}
