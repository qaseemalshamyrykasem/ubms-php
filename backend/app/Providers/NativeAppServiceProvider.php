<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NativePHP\Laravel\Facades\NativePHP;
use NativePHP\Laravel\Menu\Menu;

class NativeAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!NativePHP::isNative()) {
            return;
        }

        // Configure SQLite database for native runtime
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => storage_path('app/native/database.sqlite'),
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'sync',
        ]);

        // Mobile-specific bindings
        $this->app->singleton(
            \App\Services\Native\NotificationService::class,
            fn () => new \App\Services\Native\NotificationService()
        );

        // Register menu (for desktop variant)
        if (config('nativephp.desktop.enabled')) {
            NativePHP::menu(fn (Menu $menu) => $menu
                ->label('UBMS')
                ->appMenu()
                ->separator()
                ->link('Dashboard', '/mobile/dashboard')
                ->link('Announcements', '/mobile/announcements')
                ->link('Attendance', '/mobile/attendance')
                ->separator()
                ->quit()
            );
        }

        // Handle deep links (ubms://...)
        NativePHP::onDeeplink(function ($url) {
            // Parse ubms://lecture/{id}/qr/{token}
            $path = parse_url($url, PHP_URL_PATH);
            logger('NativePHP deeplink received', ['url' => $url]);
        });

        // Boot notification channels
        NativePHP::booted(function () {
            app(\App\Services\Native\NotificationService::class)->initialize();
        });
    }
}
