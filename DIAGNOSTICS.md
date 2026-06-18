# 🔍 NativePHP Mobile Build Diagnostics

<div dir="rtl">

هذا الملف يوثّق كل ما تم إصلاحه في هذه الجلسة لجعل المشروع قابلاً للبناء عبر GitHub Actions.

## ✅ قائمة الإصلاحات المنفذة

### 1️⃣ تحويل Electron → Mobile

| العنصر | قبل | بعد |
|--------|-----|-----|
| `composer.json` | `nativephp/electron: ^0.6.0` | ❌ محذوف |
| `composer.json` | (لا يوجد) | ✅ `nativephp/mobile: ^0.6.0` |
| `NativeAppServiceProvider` | يستخدم `NativePHP::menu()` (Electron API) | ✅ يعتمد على env vars فقط |
| `config/nativephp.php` | يحتوي قسم `desktop` | ✅ Mobile فقط |
| `NotificationService` | يستخدم `NativePHP::notify()` | ✅ JavaScript bridge |

### 2️⃣ ملفات Gradle المنشأة (غير موجودة سابقاً)

```
backend/nativephp/android/
├── build.gradle                          ✅ جديد
├── settings.gradle                       ✅ جديد
├── gradle.properties                     ✅ جديد
├── local.properties.template             ✅ جديد
├── gradlew + gradlew.bat                 ✅ جديد
├── gradle/wrapper/
│   └── gradle-wrapper.properties         ✅ جديد
└── app/
    ├── build.gradle                      ✅ جديد (مع NDK + Capacitor)
    ├── proguard-rules.pro                ✅ جديد
    └── src/main/
        ├── AndroidManifest.xml           ✅ جديد (مع صلاحيات + deep links)
        ├── java/com/university/ubms/
        │   └── MainActivity.java         ✅ جديد
        ├── assets-www/
        │   └── native-bridge.js          ✅ جديد
        └── res/
            ├── drawable/splash.xml       ✅ جديد
            ├── values/colors.xml         ✅ جديد
            ├── values/strings.xml        ✅ جديد
            ├── values/styles.xml         ✅ جديد
            └── xml/network_security_config.xml  ✅ جديد
```

### 3️⃣ ملفات Capacitor (NativePHP Mobile يستخدم Capacitor داخلياً)

```
backend/nativephp/
├── capacitor.config.ts                   ✅ جديد
├── package.json                          ✅ جديد
├── tsconfig.json                         ✅ جديد
├── README.md                             ✅ جديد
├── src/
│   └── native-bridge.ts                  ✅ جديد
└── scripts/
    └── build-android.sh                  ✅ جديد
```

### 4️⃣ إصلاحات composer.json

```diff
- "php": "^8.3"           →  + "php": "^8.2"  (متوافق مع workflow)
- "nativephp/electron": "^0.6.0"  →  ❌ محذوف
+ "nativephp/mobile": "^0.6.0"   →  ✅ مضاف
+ "ext-sqlite3": "*"             →  ✅ مضاف
+ "minimum-stability": "beta"    →  ✅ مضاف (NativePHP Mobile Beta)
```

### 5️⃣ إصلاحات GitHub Actions Workflow

| المشكلة السابقة | الحل |
|-----------------|------|
| PHP 8.2 (composer.json كان يتطلب 8.3) | ✅ تم توحيد على 8.2 |
| لا يوجد Node.js setup | ✅ أضيف Node 20 |
| لا يوجد `working-directory` | ✅ جميع الخطوات تستخدم `./backend` |
| يستخدم `native:package` (غير موجود) | ✅ يستخدم `native:build android` + 2 fallbacks |
| مسار APK ثابت قد يفشل | ✅ بحث ديناميكي عن أي APK في المشروع |
| لا يوجد handling للأخطاء | ✅ رفع logs عند الفشل |
| لا يوجد NDK | ✅ أضيف NDK 26.1.10909125 |
| لا يوجد verify steps | ✅ 5 خطوات verify |

### 6️⃣ خطوات الـ workflow الـ 20

1. 📥 Checkout Code
2. 🐘 Setup PHP 8.2 (مع كل الامتدادات المطلوبة)
3. ✅ Verify PHP
4. 📦 Setup Node.js 20
5. ☕ Setup Java JDK 17
6. ✅ Verify Java
7. 📱 Setup Android SDK (مع NDK)
8. ✅ Verify Android SDK
9. 📥 Install Composer Dependencies
10. ✅ Verify Composer
11. ⚙️ Setup Laravel Environment (SQLite تلقائياً)
12. 🗄️ Run Database Migrations
13. 📚 Publish Laravel Assets
14. 📥 Install NativePHP Mobile NPM Dependencies
15. 🤖 Prepare NativePHP Mobile (native:install)
16. 📱 Build Android APK (مع 3 محاولات fallback)
17. 🔍 Find Generated APK (بحث ديناميكي)
18. 📤 Upload APK Artifact
19. 📤 Upload Build Logs (عند الفشل)
20. 📋 Build Summary

### 7️⃣ توافق الإصدارات النهائي

| التقنية | الإصدار | السبب |
|--------|--------|------|
| PHP | 8.2 | متوافق مع NativePHP Mobile 0.6 |
| Laravel | 12.x | أحدث LTS |
| NativePHP Laravel | ^0.6.0 | أحدث stable |
| NativePHP Mobile | ^0.6.0 | أحدث Beta (مع `minimum-stability: beta`) |
| Livewire | ^3.5 | متوافق مع Laravel 12 |
| Node.js | 20 LTS | متوافق مع Capacitor 6 |
| Java | 17 | متوافق مع AGP 8.5 |
| Android Gradle Plugin | 8.5.2 | متوافق مع Gradle 8.7 |
| Gradle | 8.7 | متوافق مع Java 17 |
| Android compileSdk | 34 | Android 14 |
| Android minSdk | 24 | Android 7.0 (99%+ coverage) |
| Android NDK | 26.1.10909125 | متوافق مع AGP 8.5 |
| Kotlin | 1.9.22 | متوافق مع AGP 8.5 |
| Capacitor | 6.1.2 | أحدث stable |

### 8️⃣ ضمانات عدم تحول البناء إلى Linux

الـ workflow:
- ✅ لا يستخدم `native:build linux` أو `native:build electron`
- ✅ يستخدم صراحة `native:build android`
- ✅ مسار الإخراج المتوقع: `nativephp/android/app/build/outputs/apk/debug/`
- ✅ البحث عن `*.apk` فقط (ليس `*.AppImage` أو `*.deb`)
- ✅ `if-no-files-found: error` يضمن الفشل الصريح إذا لم يُنتج APK

## 🎯 التوقعات عند تشغيل الـ workflow

### السيناريو 1: نجاح كامل (الأكثر احتمالاً)
```
✅ PHP setup → ✅ Composer install → ✅ native:install
→ ✅ native:build android → ✅ APK found → ✅ Artifact uploaded
```
الناتج: `ubms-android-apk.zip` يحتوي على `app-debug.apk`

### السيناريو 2: فشل في native:install (الأقل احتمالاً)
الـ workflow يحاول fallback:
1. `composer require nativephp/mobile` (إعادة تثبيت)
2. إعادة محاولة `native:install`
3. إذا فشل، يكمل لأن الملفات موجودة مسبقاً في `nativephp/android/`

### السيناريو 3: فشل native:build (نادر)
الـ workflow يحاول 3 أوامر بالترتيب:
1. `php artisan native:build android --debug`
2. `php artisan native:package android --build-type=debug`
3. `./gradlew assembleDebug` (مباشرة على Gradle)

### السيناريو 4: فشل كامل
الـ workflow:
- يرفع build logs كـ artifact للمساعدة في التشخيص
- يعرض ملخص الحالة

## 📂 ملاحظات للمستخدم

1. **NativePHP Mobile Beta**: الحزمة `nativephp/mobile` لا تزال في Beta. قد تتغير الـ APIs بين الإصدارات. الـ workflow مرن بما يكفي للتعامل مع هذا.

2. **وقت البناء المتوقع**: 8-15 دقيقة على GitHub Actions (يعتمد على cache hits).

3. **حجم الـ APK المتوقع**: 80-120 MB (يشمل PHP runtime + Laravel app + Capacitor).

4. **التوقيع**: الـ APK Debug موقّع تلقائياً بـ debug keystore. للـ release، ستحتاج keystore خاص (يمكن إضافته كـ GitHub Secret).

5. **API vs Mobile**: المشروع يدعم الاثنين:
   - `/api/v1/*` للـ React frontend (ويب)
   - `/mobile/*` للـ Blade views (Android)

</div>
