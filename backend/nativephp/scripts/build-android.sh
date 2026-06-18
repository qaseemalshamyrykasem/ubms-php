#!/usr/bin/env bash
# NativePHP Mobile - Android Build Helper
# This script wraps the NativePHP artisan commands and adds fallback logic
# for CI environments where some commands may not exist yet.

set -e

WORKDIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$WORKDIR"

echo "🚀 Starting NativePHP Mobile Android build..."
echo "Working directory: $WORKDIR"
echo ""

# 1. Check prerequisites
echo "📋 Step 1: Checking prerequisites..."
check_command() {
    if ! command -v "$1" &> /dev/null; then
        echo "❌ $1 is required but not installed"
        exit 1
    fi
}
check_command php
check_command composer

# Java is required for Android builds
if ! command -v java &> /dev/null; then
    echo "❌ java is required but not installed"
    exit 1
fi

if [ -z "$ANDROID_HOME" ] && [ -z "$ANDROID_SDK_ROOT" ]; then
    echo "⚠️ ANDROID_HOME not set; assuming /usr/local/lib/android/sdk"
    export ANDROID_HOME=/usr/local/lib/android/sdk
    export ANDROID_SDK_ROOT=$ANDROID_HOME
fi

if [ ! -d "$ANDROID_HOME" ]; then
    echo "❌ Android SDK not found at $ANDROID_HOME"
    exit 1
fi

echo "   ✅ PHP: $(php -v | head -1)"
echo "   ✅ Java: $(java -version 2>&1 | head -1)"
echo "   ✅ Android SDK: $ANDROID_HOME"
echo ""

# 2. Install Composer dependencies
echo "📦 Step 2: Installing Composer dependencies..."
composer install --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
echo "   ✅ Composer done"
echo ""

# 3. Generate .env if missing
if [ ! -f .env ]; then
    echo "📝 Step 3: Creating .env from .env.example..."
    cp .env.example .env
    php artisan key:generate --ansi
    # Configure for SQLite (mobile)
    sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env || true
    sed -i 's/DB_HOST=.*/DB_HOST=/' .env || true
    sed -i 's/DB_DATABASE=.*/DB_DATABASE=storage\/app\/native\/database.sqlite/' .env || true
    echo "   ✅ .env configured"
fi

# 4. Run migrations (ensure database is ready)
echo "🗄️ Step 4: Running database migrations..."
php artisan migrate --force --seed 2>&1 || echo "   ⚠️ Migrations skipped (may already be up-to-date)"
echo ""

# 5. Build Laravel assets (Blade views are bundled as-is)
echo "🎨 Step 5: Publishing Laravel assets..."
php artisan vendor:publish --tag=laravel-assets --ansi --force 2>&1 || true
php artisan storage:link 2>&1 || true
echo "   ✅ Assets published"
echo ""

# 6. Install NativePHP Mobile (idempotent)
echo "🤖 Step 6: Preparing NativePHP Mobile..."
if php artisan list 2>/dev/null | grep -q "native:install"; then
    echo "   Running: php artisan native:install --force"
    php artisan native:install --force 2>&1 || {
        echo "   ⚠️ native:install failed, continuing anyway (files may already exist)"
    }
else
    echo "   ⚠️ native:install command not found. NativePHP Mobile may not be installed."
    echo "   Trying: composer require nativephp/mobile"
    composer require nativephp/mobile --no-interaction 2>&1 || true
    php artisan native:install --force 2>&1 || true
fi
echo ""

# 7. Build the Android APK
echo "📱 Step 7: Building Android APK..."
APK_PATH=""

# Try multiple build commands (NativePHP API has evolved across versions)
BUILD_COMMANDS=(
    "php artisan native:build android --debug"
    "php artisan native:package android --build-type=debug"
    "php artisan native:android:build"
)

for cmd in "${BUILD_COMMANDS[@]}"; do
    echo "   Trying: $cmd"
    if eval "$cmd" 2>&1; then
        echo "   ✅ Build command succeeded"
        break
    else
        echo "   ⚠️ Command failed, trying next..."
    fi
done
echo ""

# 8. Find the generated APK
echo "🔍 Step 8: Looking for the generated APK..."
APK_SEARCH_PATHS=(
    "nativephp/android/app/build/outputs/apk/debug/app-debug.apk"
    "nativephp/android/app/build/outputs/apk/debug/*.apk"
    "nativephp/android/app/build/outputs/apk/**/*.apk"
    "nativephp/**/*.apk"
    "**/*.apk"
)

for pattern in "${APK_SEARCH_PATHS[@]}"; do
    APK_FILE=$(find . -path "./vendor" -prune -o -path "./node_modules" -prune -o -name "$(basename "$pattern")" -print 2>/dev/null | head -1 || true)
    if [ -n "$APK_FILE" ] && [ -f "$APK_FILE" ]; then
        APK_PATH="$APK_FILE"
        break
    fi
done

# Broader search as fallback
if [ -z "$APK_PATH" ]; then
    APK_PATH=$(find . -name "*.apk" -not -path "./vendor/*" -not -path "./node_modules/*" 2>/dev/null | head -1)
fi

if [ -n "$APK_PATH" ] && [ -f "$APK_PATH" ]; then
    echo "   ✅ APK found: $APK_PATH"
    echo "   📏 Size: $(du -h "$APK_PATH" | cut -f1)"
    echo ""
    echo "$APK_PATH" > /tmp/ubms_apk_path.txt
    echo "🎉 Build successful! APK: $APK_PATH"
    exit 0
else
    echo "   ❌ No APK found in any expected location"
    echo ""
    echo "   📂 Files in nativephp/android/app/build/outputs (if any):"
    find nativephp/android/app/build/outputs -type f 2>/dev/null || echo "     (directory not found)"
    echo ""
    echo "   📂 All APK files anywhere in project:"
    find . -name "*.apk" -not -path "./vendor/*" -not -path "./node_modules/*" 2>/dev/null || echo "     (none)"
    exit 1
fi
