<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NativePHP App Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the NativePHP mobile/desktop app build.
    | This runs the same Laravel app inside a native shell.
    |
    */

    'app_id' => env('NATIVEPHP_APP_ID', 'com.university.ubms'),
    'app_name' => env('NATIVEPHP_APP_NAME', 'UBMS'),
    'app_version' => env('NATIVEPHP_APP_VERSION', '1.0.0'),
    'app_author' => env('NATIVEPHP_APP_AUTHOR', 'UBMS Team'),

    /*
    |--------------------------------------------------------------------------
    | Application Provider
    |--------------------------------------------------------------------------
    */
    'provider' => \App\Providers\NativeAppServiceProvider::class,

    /*
    |--------------------------------------------------------------------------
    | NativePHP Environment Detection
    |--------------------------------------------------------------------------
    */
    'env' => [
        'detect' => true,
        'key' => 'NATIVEPHP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mobile / Desktop configuration
    |--------------------------------------------------------------------------
    */
    'mobile' => [
        'enabled' => env('NATIVEPHP_MOBILE', true),
        'min_sdk' => 24, // Android 7.0+
        'target_sdk' => 34, // Android 14
        'permissions' => [
            'android.permission.CAMERA',
            'android.permission.INTERNET',
            'android.permission.ACCESS_NETWORK_STATE',
            'android.permission.VIBRATE',
            'android.permission.POST_NOTIFICATIONS',
        ],
        'deeplink_scheme' => 'ubms',
    ],

    'desktop' => [
        'enabled' => env('NATIVEPHP_DESKTOP', false),
        'window' => [
            'width' => 1280,
            'height' => 800,
            'min_width' => 1024,
            'min_height' => 600,
            'titlebar' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database (SQLite for mobile)
    |--------------------------------------------------------------------------
    */
    'database' => [
        'driver' => 'sqlite',
        'path' => storage_path('app/native/database.sqlite'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Background services
    |--------------------------------------------------------------------------
    */
    'background' => [
        'enabled' => true,
        'services' => [
            \App\Services\Native\NotificationService::class,
        ],
    ],
];
