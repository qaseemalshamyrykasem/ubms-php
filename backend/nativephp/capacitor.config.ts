import type { CapacitorConfig } from '@capacitor/cli';

/**
 * Capacitor configuration for UBMS Android app.
 *
 * The Laravel app's public/ directory serves as the webDir.
 * Capacitor bundles these assets into the Android APK.
 *
 * The PHP runtime is bundled separately by the Android app's
 * MainActivity, which starts a local php-cgi server.
 */
const config: CapacitorConfig = {
    appId: 'com.university.ubms',
    appName: 'UBMS',
    webDir: '../public',
    backgroundColor: '#0f172a',

    server: {
        androidScheme: 'https',
        hostname: 'app.ubms.local',
        port: 8000,
        // The Android app starts a local PHP server at http://localhost:8000
        // and the WebView loads from there.
        url: 'http://localhost:8000',
    },

    plugins: {
        SplashScreen: {
            launchShowDuration: 1500,
            backgroundColor: '#4F46E5',
            showSpinner: false,
            androidScaleType: 'CENTER_CROP',
            splashFullScreen: true,
            splashImmersive: true,
        },
        StatusBar: {
            style: 'DARK',
            backgroundColor: '#4F46E5',
            overlaysWebView: false,
        },
        Camera: {
            permissions: ['camera'],
        },
        LocalNotifications: {
            smallIcon: 'ic_stat_icon',
            iconColor: '#4F46E5',
            sound: 'notification.wav',
        },
        Haptics: {},
        Preferences: {},
    },

    android: {
        buildOptions: {
            keystorePath: 'debug.keystore',
            keystoreAlias: 'androiddebugkey',
            keystoreAliasPassword: 'android',
            keystorePassword: 'android',
        },
        allowMixedContent: true,
        captureInput: true,
        webContentsDebuggingEnabled: true,
    },
};

export default config;
