<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * NativeBuildAndroidCommand
 *
 * Builds the Android APK via Gradle.
 *
 * This command:
 *   1. Verifies that native:install has been run (android/ directory exists)
 *   2. Verifies that the Android SDK is available
 *   3. Runs `./gradlew assembleDebug` to build the APK
 *   4. Locates the generated APK and prints its path
 *
 * Usage:
 *   php artisan native:build android
 *   php artisan native:build android --release   (builds a release APK)
 */
class NativeBuildAndroidCommand extends Command
{
    protected $signature = 'native:build {platform : android (only android is supported)} {--release : Build a release APK instead of debug}';
    protected $description = 'Build the Android APK via Gradle';

    public function handle(): int
    {
        $platform = $this->argument('platform');

        if ($platform !== 'android') {
            $this->error("❌ Only 'android' platform is supported. Got: $platform");
            return Command::FAILURE;
        }

        $buildType = $this->option('release') ? 'release' : 'debug';
        $gradleTask = $buildType === 'release' ? 'assembleRelease' : 'assembleDebug';

        $this->info("📱 Building Android APK (build type: $buildType)...");
        $this->newLine();

        // Step 1: Verify android project
        if (!$this->verifyAndroidProject()) {
            return Command::FAILURE;
        }

        // Step 2: Verify environment
        if (!$this->verifyEnvironment()) {
            return Command::FAILURE;
        }

        // Step 3: Run Gradle build
        if (!$this->runGradleBuild($gradleTask, $buildType)) {
            return Command::FAILURE;
        }

        // Step 4: Locate and report the APK
        $apkPath = $this->locateApk($buildType);
        if ($apkPath === null) {
            $this->error('❌ APK was not found after build.');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Android APK built successfully!');
        $this->info("   Path: $apkPath");
        $this->info('   Size: ' . $this->formatSize(filesize($apkPath)));

        return Command::SUCCESS;
    }

    private function verifyAndroidProject(): bool
    {
        $this->line('📂 Step 1: Verifying Android project...');

        $androidDir = base_path('nativephp/android');
        if (!is_dir($androidDir)) {
            $this->error("   ❌ nativephp/android/ not found.");
            $this->line('   Run `php artisan native:install` first.');
            return false;
        }

        $gradlew = "$androidDir/gradlew";
        if (!file_exists($gradlew)) {
            $this->error("   ❌ gradlew not found at $gradlew");
            return false;
        }

        // Make gradlew executable
        if (!is_executable($gradlew)) {
            @chmod($gradlew, 0755);
        }

        $buildGradle = "$androidDir/app/build.gradle";
        if (!file_exists($buildGradle)) {
            $this->error("   ❌ app/build.gradle not found at $buildGradle");
            return false;
        }

        $this->line('   ✅ Android project structure verified');
        return true;
    }

    private function verifyEnvironment(): bool
    {
        $this->line('🔧 Step 2: Verifying build environment...');

        // Check Java
        $output = [];
        $exitCode = 0;
        exec('java -version 2>&1', $output, $exitCode);
        if ($exitCode !== 0) {
            $this->error('   ❌ Java is not available. Install JDK 17.');
            return false;
        }
        $this->line('   ✅ Java: ' . ($output[0] ?? 'available'));

        // Check Android SDK
        $androidHome = getenv('ANDROID_HOME') ?: getenv('ANDROID_SDK_ROOT');
        if (!$androidHome || !is_dir($androidHome)) {
            $this->warn('   ⚠️ ANDROID_HOME not set or invalid. Gradle may fail to find the SDK.');
            $this->line('   Set ANDROID_HOME to your Android SDK path.');
        } else {
            $this->line("   ✅ ANDROID_HOME: $androidHome");
        }

        // Generate local.properties if missing
        $localProps = base_path('nativephp/android/local.properties');
        if (!file_exists($localProps) && $androidHome) {
            File::put($localProps, "sdk.dir=$androidHome\n");
            $this->line('   📝 Created local.properties');
        }

        return true;
    }

    private function runGradleBuild(string $task, string $buildType): bool
    {
        $this->line("🔨 Step 3: Running Gradle $task...");

        $androidDir = base_path('nativephp/android');
        $cmd = "cd {$androidDir} && ./gradlew {$task} --no-daemon --stacktrace 2>&1";

        $this->line("   Command: $cmd");
        $this->line('   (this may take several minutes)...');
        $this->newLine();

        // Stream output in real-time
        $handle = popen($cmd, 'r');
        if ($handle === false) {
            $this->error('   ❌ Failed to execute Gradle.');
            return false;
        }

        $output = [];
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;
            $output[] = $line;
            // Only show last 5 lines to keep output manageable
        }
        pclose($handle);

        $exitCode = 0;
        $fullOutput = implode('', $output);

        // Check for BUILD SUCCESSFUL or BUILD FAILED
        if (strpos($fullOutput, 'BUILD SUCCESSFUL') !== false) {
            $this->line('   ✅ Gradle build succeeded');
            return true;
        }

        if (strpos($fullOutput, 'BUILD FAILED') !== false) {
            $this->error('   ❌ Gradle build failed!');
            $this->newLine();
            $this->line('   Last 30 lines of output:');
            $this->line(implode('', array_slice($output, -30)));
            return false;
        }

        // Unknown status — show tail
        $this->warn('   ⚠️ Build status unknown. Showing last output:');
        $this->line(implode('', array_slice($output, -10)));
        return false;
    }

    private function locateApk(string $buildType): ?string
    {
        $this->line('🔍 Step 4: Locating generated APK...');

        $apkDir = base_path("nativephp/android/app/build/outputs/apk/{$buildType}");
        if (!is_dir($apkDir)) {
            $this->error("   ❌ APK output directory not found: $apkDir");
            return null;
        }

        $apkFiles = glob("$apkDir/*.apk");
        if (empty($apkFiles)) {
            $this->error("   ❌ No APK files found in $apkDir");
            return null;
        }

        $apkPath = $apkFiles[0];
        $this->line("   ✅ Found: $apkPath");
        return $apkPath;
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
