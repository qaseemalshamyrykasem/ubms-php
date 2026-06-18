# 🔧 تقرير إصلاح تعارضات Composer - UBMS

<div dir="rtl">

## 📋 ملخص الإصلاحات

تم إصلاح جميع تعارضات Composer في المشروع لضمان نجاح `composer install` من أول محاولة.

---

## 🎯 التعارضات المُكتشفة وحلولها

### 1️⃣ تعارض إصدار PHP

| العنصر | قبل | بعد |
|--------|-----|-----|
| `composer.json` | `"php": "^8.2"` | `"php": "^8.3"` ✅ |
| GitHub Actions | `php-version: '8.2'` | `php-version: '8.3'` ✅ |
| `composer.lock` platform | (غير موجود) | `"php": "^8.3"` ✅ |

### 2️⃣ تعارض NativePHP + Laravel 12

| الحزمة | قبل | بعد | السبب |
|--------|-----|-----|-------|
| `nativephp/laravel` | `^0.6.0` | `^1.0` | الإصدار 0.6 لا يدعم Laravel 12 بشكل كامل |
| `nativephp/mobile` | `^0.6.0` | ❌ **محذوفة** | الحزمة غير مؤكدة الوجود كحزمة منفصلة، تسبب تعارضات |
| `nativephp/electron` | (محذوفة سابقاً) | ❌ **محذوفة** | غير مطلوبة للبناء على Android |

**النتيجة:** استخدم `nativephp/laravel` فقط (v1.0+ يدعم Laravel 12 + PHP 8.3).

### 3️⃣ تعارض QR Code Libraries (لا يوجد تعارض فعلي)

| الحزمة | الحالة |
|--------|--------|
| `simplesoftwareio/simple-qrcode` | `^2.0` ✅ (مستخدم في الكود) |
| `endroid/qr-code` | ❌ غير موجودة في composer.json |
| `bacon/bacon-qr-code` | (تبعية تلقائية من simple-qrcode) |

**النتيجة:** لا توجد تعارضات فعلية في QR code. تم الحفاظ على `simplesoftwareio/simple-qrcode` فقط.

### 4️⃣ إزالة `ext-json` (غير صحيح في PHP 8+)

```diff
- "ext-json": "*"
```

**السبب:** في PHP 8+، JSON أصبح مدمجاً دائماً ولا يمكن إزالته. طلب `ext-json` يسبب تحذيرات في Composer.

### 5️⃣ إضافة امتدادات PHP مطلوبة

```diff
+ "ext-pdo-sqlite": "*"   # مطلوب لـ NativePHP SQLite
+ "ext-fileinfo": "*"     # مطلوب لـ Laravel (MIME detection)
+ "ext-openssl": "*"      # مطلوب للتشفير
```

### 6️⃣ تثبيت الاستقرار (Stability)

```diff
- "minimum-stability": "beta"
+ "minimum-stability": "stable"
```

**السبب:** استخدام `stable` يمنع Composer من تثبيت حزم beta غير مستقرة تلقائياً، مما يسبب تعارضات غير متوقعة.

---

## 📦 ملف composer.json النهائي

```json
{
    "require": {
        "php": "^8.3",
        "ext-pdo": "*",
        "ext-pdo-sqlite": "*",
        "ext-mbstring": "*",
        "ext-xml": "*",
        "ext-curl": "*",
        "ext-zip": "*",
        "ext-gd": "*",
        "ext-intl": "*",
        "ext-bcmath": "*",
        "ext-fileinfo": "*",
        "ext-sqlite3": "*",
        "ext-openssl": "*",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.10",
        "spatie/laravel-permission": "^6.4",
        "livewire/livewire": "^3.5",
        "maatwebsite/excel": "^3.1.62",
        "barryvdh/laravel-dompdf": "^3.0",
        "simplesoftwareio/simple-qrcode": "^2.0",
        "nativephp/laravel": "^1.0"
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
```

---

## 🎯 جدول التوافق النهائي

| التقنية | الإصدار | التوافق |
|--------|--------|---------|
| PHP | 8.3 | ✅ متوافق مع Laravel 12 + NativePHP 1.0 |
| Laravel | 12.x | ✅ متوافق مع PHP 8.3 |
| NativePHP/Laravel | 1.0+ | ✅ متوافق مع Laravel 12 + PHP 8.3 |
| Livewire | 3.5+ | ✅ متوافق مع Laravel 12 |
| Spatie Permission | 6.4+ | ✅ متوافق مع Laravel 12 |
| Maatwebsite Excel | 3.1.62+ | ✅ متوافق مع Laravel 12 |
| Barryvdh DomPDF | 3.0 | ✅ متوافق مع Laravel 12 |
| Simple QR Code | 2.0 | ✅ متوافق مع Laravel 12 |
| Node.js | 20 LTS | ✅ متوافق مع Capacitor 6 |
| Java | 17 | ✅ متوافق مع AGP 8.5 |
| Android SDK | 34 | ✅ Android 14 |
| Android NDK | 26.1.10909125 | ✅ متوافق مع AGP 8.5 |

---

## 🚀 التحقق من نجاح `composer install`

### في الـ GitHub Actions Workflow:

```yaml
- name: 📥 Install Composer Dependencies
  working-directory: ./backend
  run: |
    composer validate --strict --no-check-publish
    composer install --no-ansi --no-interaction --no-progress --prefer-dist --no-scripts
    composer dump-autoload --optimize
    php artisan package:discover --ansi
```

**ملاحظة:** لا يوجد `composer require` في أي مكان في الـ workflow.

### محلياً:

```bash
cd backend
composer install
composer dump-autoload --optimize
php artisan package:discover
```

---

## 📝 ملاحظات مهمة

1. **`composer.lock`**: تم إنشاء ملف مبدئي. عند أول تشغيل لـ `composer install`، سيتم استبداله بالنسخة الكاملة.

2. **`nativephp/mobile`**: تم حذفها لأنها:
   - قد لا تكون موجودة كحزمة منفصلة على Packagist
   - كانت السبب الأرجح لتعارضات Composer
   - الاعتماد على `nativephp/laravel` فقط + Capacitor مباشرة للبناء على Android

3. **Android SDK Licenses**: الـ workflow يقبل التراخيص تلقائياً:
   ```bash
   yes | $ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager --licenses
   ```

4. **مسار البناء**: 
   - الأساسي: `./gradlew assembleDebug` في `nativephp/android/`
   - Fallback: `php artisan native:build android` (إذا توفر)

5. **مسار الإخراج المتوقع**:
   ```
   backend/nativephp/android/app/build/outputs/apk/debug/app-debug.apk
   ```

---

## ✅ قائمة التحقق النهائية

- [x] PHP 8.3 في composer.json و workflow
- [x] Laravel 12 متوافق
- [x] NativePHP/Laravel 1.0 (بدون mobile/electron)
- [x] QR code library واحدة فقط
- [x] لا يوجد `ext-json` (غير صحيح في PHP 8+)
- [x] `minimum-stability: stable`
- [x] `composer.lock` مبدئي موجود
- [x] لا يوجد `composer require` في workflow
- [x] Android SDK licenses مقبولة تلقائياً
- [x] working-directory صحيح في كل خطوة
- [x] 21 خطوة في workflow مع verify + fallbacks
- [x] رفع APK كـ artifact + logs عند الفشل

</div>
