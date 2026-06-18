# 📱 NativePHP Mobile — Android Build

<div dir="rtl">

هذا المجلد يحتوي على إعدادات بناء تطبيق أندرويد (APK) باستخدام **NativePHP Mobile**.

## 🏗️ البنية

```
backend/
├── config/nativephp.php              # تكوين NativePHP لـ Laravel
├── nativephp.config.json             # إعدادات التطبيق (Android/iOS)
└── nativephp/                        # مجلد البناء الفعلي
    ├── android/                      # مشروع Android الأصلي
    │   ├── build.gradle              # Gradle الجذري
    │   ├── settings.gradle
    │   ├── gradle.properties
    │   ├── local.properties.template # نسخة قبل الإعداد
    │   ├── gradlew                   # سكريبت Gradle (Unix)
    │   ├── gradlew.bat               # سكريبت Gradle (Windows)
    │   ├── gradle/wrapper/
    │   │   └── gradle-wrapper.properties
    │   └── app/                      # وحدة التطبيق
    │       ├── build.gradle          # إعدادات build الوحدة
    │       ├── proguard-rules.pro
    │       └── src/main/
    │           ├── AndroidManifest.xml
    │           ├── java/com/university/ubms/
    │           │   └── MainActivity.java
    │           ├── assets-www/
    │           │   └── native-bridge.js   # JS bridge للـ Capacitor
    │           └── res/
    │               ├── drawable/splash.xml
    │               ├── values/{colors,strings,styles}.xml
    │               └── xml/network_security_config.xml
    ├── src/
    │   └── native-bridge.ts          # TypeScript bridge (مصدر)
    ├── capacitor.config.ts           # إعدادات Capacitor
    ├── package.json                  # اعتماديات NPM للـ native
    ├── tsconfig.json
    └── scripts/
        └── build-android.sh          # سكريبت بناء مساعد
```

## 🚀 البناء المحلي

### المتطلبات
- PHP 8.2+
- Composer 2.7+
- Node.js 20+
- Java JDK 17
- Android SDK (API 24 + 34, Build Tools 34.0.0, NDK 26.1.10909125)
- Android Studio (اختياري، للتعديل اليدوي)

### الخطوات

```bash
cd backend

# 1. تثبيت اعتماديات PHP
composer install

# 2. إعداد البيئة
cp .env.example .env
php artisan key:generate

# 3. إعداد قاعدة بيانات SQLite (للـ mobile)
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=storage\/app\/native\/database.sqlite/' .env

# 4. تشغيل التهجرات
mkdir -p storage/app/native
php artisan migrate --force --seed

# 5. تثبيت اعتماديات NativePHP NPM
cd nativephp && npm install && cd ..

# 6. إعداد NativePHP Mobile (يولّد gradle wrapper وملفات إضافية)
php artisan native:install --force

# 7. بناء APK
php artisan native:build android --debug

# أو عبر سكريبت مساعد:
./nativephp/scripts/build-android.sh

# الناتج:
# nativephp/android/app/build/outputs/apk/debug/app-debug.apk
```

## 🤖 البناء عبر GitHub Actions

الـ workflow في `.github/workflows/build-android.yml` يقوم تلقائياً بـ:

1. إعداد PHP 8.2 + Node 20 + Java 17 + Android SDK
2. تثبيت اعتماديات Composer + NPM
3. إعداد بيئة Laravel (SQLite)
4. تشغيل التهجرات
5. تثبيت NativePHP Mobile
6. بناء APK
7. رفعه كـ artifact قابل للتنزيل

### لتفعيل البناء:
```bash
git push origin main
# ثم اذهب إلى تبويب Actions في GitHub
# نزّل artifact: ubms-android-apk
```

## ⚙️ التخصيص

### تغيير اسم التطبيق
عدّل `nativephp.config.json` → `app.name` و `android.appName`

### تغيير أيقونة التطبيق
ضع الأيقونات في:
```
backend/nativephp/android/app/src/main/res/
├── mipmap-mdpi/ic_launcher.png      (48x48)
├── mipmap-hdpi/ic_launcher.png      (72x72)
├── mipmap-xhdpi/ic_launcher.png     (96x96)
├── mipmap-xxhdpi/ic_launcher.png    (144x144)
└── mipmap-xxxhdpi/ic_launcher.png   (192x192)
```

### إضافة صلاحيات أندرويد
عدّل `nativephp/android/app/src/main/AndroidManifest.xml`

### تغيير اسم الحزمة (package name)
عدّل في 3 أماكن:
1. `nativephp.config.json` → `android.package`
2. `nativephp/android/app/build.gradle` → `applicationId`
3. `nativephp/android/app/src/main/AndroidManifest.xml` → `package`

## 🔍 استكشاف الأخطاء

### فشل `native:install`
- تأكد أن `nativephp/mobile` مثبتة: `composer require nativephp/mobile`
- تحقق من `composer.json` يحتوي `"minimum-stability": "beta"`

### فشل Gradle
- تأكد أن Java 17 مثبت: `java -version`
- تأكد أن `ANDROID_HOME` مضبوط
- امسح الكاش: `cd nativephp/android && ./gradlew clean`

### APK لا يُنتج
- ابحث في كل المشروع: `find . -name "*.apk"`
- راجع logs: `backend/nativephp/android/app/build/reports/`

## 📚 مراجع

- [NativePHP Mobile Docs](https://nativephp.com/docs/mobile)
- [Capacitor Docs](https://capacitorjs.com/docs)
- [Android Build Docs](https://developer.android.com/build)

</div>
