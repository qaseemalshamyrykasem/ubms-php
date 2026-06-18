<?php

namespace App\Services\Native;

use NativePHP\Laravel\Facades\NativePHP;

class CameraService
{
    /**
     * Trigger native camera for QR code scanning.
     * On Android/iOS, opens the native camera scanner.
     * On web/development, returns null (use JS scanner instead).
     */
    public function scanQr(): ?string
    {
        if (!NativePHP::isNative()) {
            return null;
        }

        // NativePHP Mobile exposes camera via native bridge
        // The actual scanning happens in the WebView via JavaScript bridge
        // This method is for triggering from PHP context (e.g., scheduled scans)
        return NativePHP::call('camera.scanQr');
    }

    /**
     * Capture a photo (for avatars, profile pictures).
     */
    public function capturePhoto(string $destinationPath): ?string
    {
        if (!NativePHP::isNative()) {
            return null;
        }

        $result = NativePHP::call('camera.capture', ['destination' => $destinationPath]);
        return $result['path'] ?? null;
    }
}
