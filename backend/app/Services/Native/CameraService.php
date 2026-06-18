<?php

namespace App\Services\Native;

/**
 * CameraService
 *
 * Handles camera access for the mobile app.
 * QR code scanning and photo capture are dispatched to the
 * Capacitor Camera plugin via JavaScript bridge.
 */
class CameraService
{
    /**
     * Trigger QR code scanning.
     * The actual scanning happens on the JavaScript side via
     * the Capacitor Camera plugin or the @capacitor-community/barcode-scanner.
     *
     * @return string|null The scanned payload, or null if unsupported.
     */
    public function scanQr(): ?string
    {
        // PHP cannot directly access the camera.
        // The QR scan flow is initiated by JavaScript in the Blade view.
        return null;
    }

    /**
     * Capture a photo (e.g., for avatar upload).
     *
     * @param string $destinationPath Where to store the captured image.
     * @return string|null The path to the captured image, or null on failure.
     */
    public function capturePhoto(string $destinationPath): ?string
    {
        // Photo capture is handled by the JavaScript bridge.
        // PHP-side processing of the uploaded file happens via standard
        // Laravel file upload mechanisms.
        return null;
    }

    /**
     * Check if the camera is available on this device.
     */
    public function isAvailable(): bool
    {
        // On native runtime, camera is always available (permission permitting).
        return env('NATIVEPHP_RUNNING', false) === true;
    }
}
