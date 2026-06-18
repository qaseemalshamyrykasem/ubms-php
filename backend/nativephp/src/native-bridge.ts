// NativePHP Mobile JavaScript bridge
// Connects the WebView (Laravel/Blade) with native Capacitor plugins.

declare global {
    interface Window {
        nativephp?: {
            postMessage: (message: string) => void;
        };
        Capacitor?: any;
    }
}

// Helper functions exposed to Blade views
window.UBMSNative = {
    /**
     * Trigger device vibration.
     */
    async vibrate(duration: number = 100): Promise<void> {
        try {
            const { Haptics, ImpactStyle } = await import('@capacitor/haptics');
            await Haptics.impact({ style: ImpactStyle.Light, duration });
        } catch (e) {
            // Fallback: Web Vibration API
            if ('vibrate' in navigator) {
                (navigator as any).vibrate(duration);
            }
        }
    },

    /**
     * Show a local notification.
     */
    async notify(title: string, body: string, schedule?: { at: Date }): Promise<void> {
        try {
            const { LocalNotifications } = await import('@capacitor/local-notifications');
            await LocalNotifications.schedule({
                notifications: [{
                    id: Date.now(),
                    title,
                    body,
                    schedule: schedule ? { at: schedule.at } : undefined,
                }],
            });
        } catch (e) {
            console.warn('Local notification failed:', e);
        }
    },

    /**
     * Scan a QR code using native camera.
     */
    async scanQr(): Promise<string | null> {
        try {
            const { Camera } = await import('@capacitor/camera');
            const result = await Camera.getPhoto({
                quality: 90,
                allowEditing: false,
                resultType: 'base64',
                source: 'CAMERO',
            });
            return result.base64String || null;
        } catch (e) {
            console.warn('Camera access failed:', e);
            return null;
        }
    },

    /**
     * Set the status bar color.
     */
    async setStatusBar(color: string = '#4F46E5'): Promise<void> {
        try {
            const { StatusBar, Style } = await import('@capacitor/status-bar');
            await StatusBar.setBackgroundColor({ color });
            await StatusBar.setStyle({ style: Style.Dark });
        } catch (e) {
            // Not on native runtime
        }
    },

    /**
     * Hide the splash screen.
     */
    async hideSplash(): Promise<void> {
        try {
            const { SplashScreen } = await import('@capacitor/splash-screen');
            await SplashScreen.hide();
        } catch (e) {
            // Not on native runtime
        }
    },
};

// Auto-init on page load
if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        if (window.Capacitor?.isNativePlatform?.()) {
            window.UBMSNative.setStatusBar();
            setTimeout(() => window.UBMSNative.hideSplash(), 1500);
        }
    });
}

export {};
