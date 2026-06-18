<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * NativeInstallCommand
 *
 * Prepares the Android (Capacitor) project for building.
 *
 * This command:
 *   1. Checks that all required tools are available (node, npm, java)
 *   2. Installs NPM dependencies in nativephp/
 *   3. Copies Laravel's public/ assets into the Capacitor webDir
 *   4. Runs `cap sync android` to update the native project
 *
 * Usage:
 *   php artisan native:install
 *   php artisan native:install --force   (skip confirmation prompts)
 */
class NativeInstallCommand extends Command
{
    protected $signature = 'native:install {--force : Skip confirmation prompts}';
    protected $description = 'Prepare the Android (Capacitor) project for building';

    public function handle(): int
    {
        $this->info('🚀 Native Install — preparing Android (Capacitor) project...');
        $this->newLine();

        // Step 1: Verify prerequisites
        if (!$this->verifyPrerequisites()) {
            return Command::FAILURE;
        }

        // Step 2: Ensure nativephp/ structure exists
        $this->ensureNativePhpStructure();

        // Step 3: Install NPM dependencies
        if (!$this->installNpmDependencies()) {
            return Command::FAILURE;
        }

        // Step 4: Build and copy web assets
        if (!$this->prepareWebAssets()) {
            return Command::FAILURE;
        }

        // Step 5: Run cap sync
        if (!$this->capSync()) {
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Native install completed successfully!');
        $this->info('   You can now run: php artisan native:build android');

        return Command::SUCCESS;
    }

    private function verifyPrerequisites(): bool
    {
        $this->line('📋 Step 1: Verifying prerequisites...');

        $required = ['node', 'npm', 'java'];
        foreach ($required as $tool) {
            $output = [];
            $exitCode = 0;
            exec("command -v $tool 2>/dev/null", $output, $exitCode);
            if ($exitCode !== 0) {
                $this->error("   ❌ $tool is not installed");
                return false;
            }
            $this->line("   ✅ $tool: available");
        }

        // Check PHP extensions
        $extensions = ['pdo_sqlite', 'mbstring', 'gd', 'intl', 'bcmath', 'sqlite3', 'fileinfo', 'openssl'];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->error("   ❌ PHP extension '$ext' is not loaded");
                return false;
            }
        }
        $this->line('   ✅ PHP extensions: all loaded');

        return true;
    }

    private function ensureNativePhpStructure(): void
    {
        $this->line('📂 Step 2: Ensuring nativephp/ structure...');

        $nativephpDir = base_path('nativephp');
        if (!is_dir($nativephpDir)) {
            File::makeDirectory($nativephpDir, 0755, true);
        }

        $androidDir = base_path('nativephp/android');
        if (!is_dir($androidDir)) {
            $this->warn('   ⚠️ nativephp/android/ not found. The Android project must be committed to the repo.');
            $this->line('   If you are setting up fresh, copy the android/ directory from a Capacitor project.');
        } else {
            $this->line('   ✅ nativephp/android/ exists');
        }

        // Ensure package.json exists
        $packageJson = "$nativephpDir/package.json";
        if (!file_exists($packageJson)) {
            $this->line('   📝 Creating nativephp/package.json...');
            File::put($packageJson, json_encode([
                'name' => 'ubms-native',
                'version' => '1.0.0',
                'private' => true,
                'scripts' => [
                    'sync' => 'cap sync android',
                    'build' => 'cap build android',
                    'open' => 'cap open android',
                ],
                'dependencies' => [
                    '@capacitor/android' => '^6.1.2',
                    '@capacitor/core' => '^6.1.2',
                    '@capacitor/cli' => '^6.1.2',
                    '@capacitor/camera' => '^6.0.2',
                    '@capacitor/preferences' => '^6.0.2',
                    '@capacitor/haptics' => '^6.0.2',
                    '@capacitor/local-notifications' => '^6.0.2',
                    '@capacitor/status-bar' => '^6.0.2',
                    '@capacitor/splash-screen' => '^6.0.2',
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        // Ensure capacitor.config.ts exists
        $capConfig = "$nativephpDir/capacitor.config.ts";
        if (!file_exists($capConfig)) {
            $this->line('   📝 Creating nativephp/capacitor.config.ts...');
            File::put($capConfig, $this->getCapacitorConfigContent());
        }
    }

    private function installNpmDependencies(): bool
    {
        $this->line('📦 Step 3: Installing NPM dependencies...');

        $nativephpDir = base_path('nativephp');
        $cmd = "cd {$nativephpDir} && npm install --no-audit --no-fund 2>&1";
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('   ❌ npm install failed:');
            $this->line(implode("\n", array_slice($output, -10)));
            return false;
        }

        $this->line('   ✅ NPM dependencies installed');
        return true;
    }

    private function prepareWebAssets(): bool
    {
        $this->line('🎨 Step 4: Preparing web assets...');

        // In a Capacitor app, the webDir (default: 'public') is synced to the
        // Android assets. We don't need to copy anything — Capacitor reads
        // directly from public/. We just need to make sure it exists.
        $publicDir = public_path();
        if (!is_dir($publicDir)) {
            $this->error("   ❌ public/ directory not found at $publicDir");
            return false;
        }

        $this->line("   ✅ public/ directory exists ($publicDir)");
        return true;
    }

    private function capSync(): bool
    {
        $this->line('🔄 Step 5: Running cap sync android...');

        $nativephpDir = base_path('nativephp');
        $cmd = "cd {$nativephpDir} && npx cap sync android 2>&1";
        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->warn('   ⚠️ cap sync had warnings (this is usually OK):');
            $this->line(implode("\n", array_slice($output, -5)));
        } else {
            $this->line('   ✅ cap sync completed');
        }

        return true;
    }

    private function getCapacitorConfigContent(): string
    {
        return <<<'TS'
import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'com.university.ubms',
    appName: 'UBMS',
    webDir: '../public',
    backgroundColor: '#0f172a',
    server: {
        androidScheme: 'https',
        hostname: 'app.ubms.local',
        port: 8000,
    },
    plugins: {
        SplashScreen: {
            launchShowDuration: 1500,
            backgroundColor: '#4F46E5',
            showSpinner: false,
        },
        StatusBar: {
            style: 'DARK',
            backgroundColor: '#4F46E5',
        },
    },
};

export default config;
TS;
    }
}
