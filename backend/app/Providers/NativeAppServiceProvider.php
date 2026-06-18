<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * NativeAppServiceProvider
 *
 * Configures the Laravel runtime for the Android app (Capacitor WebView).
 *
 * When the app runs inside the Android WebView, we set NATIVEPHP_RUNNING=true
 * in the environment. This provider detects that flag and switches the
 * database to SQLite (the bundled local database) for offline-first operation.
 *
 * This provider does NOT depend on any NativePHP package. It works with
 * vanilla Laravel 12 + Capacitor.
 */
class NativeAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->isNativeRuntime()) {
            $this->app->singleton(
                \App\Services\Native\NotificationService::class,
                fn () => new \App\Services\Native\NotificationService()
            );

            $this->app->singleton(
                \App\Services\Native\CameraService::class,
                fn () => new \App\Services\Native\CameraService()
            );
        }
    }

    public function boot(): void
    {
        if (!$this->isNativeRuntime()) {
            return;
        }

        $this->configureNativeRuntime();
        $this->initializeNativeServices();
    }

    /**
     * Detect if we're running inside the Android WebView (Capacitor).
     */
    private function isNativeRuntime(): bool
    {
        return env('NATIVEPHP_RUNNING', false) === true
            || (isset($_ENV['NATIVEPHP_RUNNING']) && $_ENV['NATIVEPHP_RUNNING'])
            || (isset($_SERVER['NATIVEPHP_RUNNING']) && $_SERVER['NATIVEPHP_RUNNING']);
    }

    /**
     * Configure the Laravel runtime for the Android WebView.
     */
    private function configureNativeRuntime(): void
    {
        $sqlitePath = storage_path('app/native/database.sqlite');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $sqlitePath,
            'database.connections.sqlite.driver' => 'sqlite',
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
            'filesystems.default' => 'local',
        ]);

        if (!file_exists($sqlitePath)) {
            $dir = dirname($sqlitePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if (!file_exists($sqlitePath)) {
                @touch($sqlitePath);
            }
        }
    }

    /**
     * Initialize native services.
     */
    private function initializeNativeServices(): void
    {
        try {
            $notificationService = $this->app->make(\App\Services\Native\NotificationService::class);
            $notificationService->initialize();
        } catch (\Throwable $e) {
            logger()->warning('Native notification service failed to initialize: ' . $e->getMessage());
        }
    }
}
