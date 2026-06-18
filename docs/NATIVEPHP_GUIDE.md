# 📱 دليل NativePHP Mobile - UBMS

<div dir="rtl">

## 🎯 ما تم إنجازه

تم تحويل مشروع UBMS ليدعم **NativePHP Mobile** مع:

### ✅ الإضافات
1. **حزمة NativePHP + Livewire** في `composer.json`
2. **ملف تكوين** `config/nativephp.php`
3. **NativeAppServiceProvider** للتكوين التلقائي
4. **خدمات Native**:
   - `NotificationService` - إشعارات native + اهتزاز
   - `CameraService` - للوصول للكاميرا
5. **18 صفحة Blade** للهاتف:
   - تسجيل الدخول / إنشاء حساب / استعادة كلمة المرور
   - لوحة التحكم (طالب + ممثل)
   - الإعلانات (قائمة + تفاصيل + إنشاء)
   - الحضور (إحصائيات + QR Scanner + تفاصيل المحاضرة + إنشاء)
   - الواجبات (قائمة + تفاصيل)
   - الجدول الأسبوعي
   - الإشعارات
   - الملف الشخصي (عرض + تعديل + كلمة المرور)
   - تيليجرام (ربط + فصل)
6. **MobileController** يدير كل الـ routes
7. **routes/mobile.php** منفصل
8. **Bottom Navigation** عربي RTL
9. **QR Scanner** مع كاميرا native + fallback HTML5

### 🎨 التصميم
- **Dark Mode افتراضي** (مطابق لتطبيقات الجوال الحديثة)
- **RTL** عربي أولاً
- **Safe Area** support (notch, status bar)
- **Touch-friendly** أزرار كبيرة (44px+)
- **Bottom Navigation** بـ 5 أيقونات
- **Glassmorphism** effects
- **Smooth animations**

---

## 🛠️ المتطلبات للبناء

### 1. على جهازك (مطلوب لبناء APK)
- **PHP 8.3+** مع امتدادات: pdo_sqlite, mbstring, gd, zip
- **Composer 2.7+**
- **Android Studio** (Hedgehog أو أحدث)
- **Android SDK** (API 24 - 34)
- **Android NDK** (لـ PHP binary)
- **Java JDK 17**
- **Node.js 20+** (لبناء assets إن لزم)

### 2. تثبيت NativePHP Mobile
```bash
# بعد فك ضغط المشروع
cd backend
composer install

# تثبيت NativePHP Mobile (Beta)
composer require nativephp/mobile:@beta --with-all-dependencies

# نشر ملفات التكوين
php artisan vendor:publish --tag=nativephp-mobile-config
```

---

## 🚀 خطوات البناء

### الخطوة 1: إعداد المشروع
```bash
cd backend
cp .env.example .env
php artisan key:generate

# تأكد من إعداد قاعدة البيانات
# للـ mobile: استخدم SQLite
# DB_CONNECTION=sqlite
# DB_DATABASE=storage/app/native/database.sqlite
```

### الخطوة 2: تشغيل التهجرات
```bash
# إنشاء قاعدة بيانات SQLite
touch storage/app/native/database.sqlite

# تشغيل التهجرات + بيانات تجريبية
php artisan migrate --seed
```

### الخطوة 3: اختبار محلي
```bash
# شغّل خادم الويب لاختبار الواجهة
php artisan serve

# افتح: http://localhost:8000/mobile/login
# جرّب: rep@ubms.local / password
```

### الخطوة 4: تهيئة NativePHP
```bash
# تهيئة مشروع Android
php artisan native:install android

# هذا سينشئ مجلد android/ مع:
# - AndroidManifest.xml
# - build.gradle
# - MainActivity.java
# - PHP binaries (مُجمّعة لـ ARM64)
```

### الخطوة 5: بناء APK
```bash
# خيار أ: APK Debug (سريع)
php artisan native:build android --debug

# خيار ب: APK Release (مُحسّن، يتطلب keystore)
php artisan native:build android --release

# الناتج: android/app/build/outputs/apk/release/app-release.apk
```

### الخطوة 6: فتح في Android Studio (للتخصيص)
```bash
php artisan native:open android
# سيفتح Android Studio
```

---

## 📱 الصفحات المتاحة في التطبيق

### للطالب (Student)
| الصفحة | URL | الوظيفة |
|--------|-----|---------|
| تسجيل الدخول | `/mobile/login` | دخول بحساب موجود |
| إنشاء حساب | `/mobile/register` | تسجيل طالب جديد |
| استعادة كلمة المرور | `/mobile/forgot-password` | إرسال رابط إعادة التعيين |
| لوحة التحكم | `/mobile/dashboard` | إحصائيات + إعلانات + واجبات |
| الإعلانات | `/mobile/announcements` | قائمة + بحث + تصفية |
| تفاصيل إعلان | `/mobile/announcements/{id}` | عرض كامل + تنزيل مرفقات |
| الحضور | `/mobile/attendance` | إحصائيات + سجل |
| مسح QR | `/mobile/attendance/scan` | كاميرا native لتسجيل الحضور |
| الواجبات | `/mobile/assignments` | قائمة + تنزيل |
| الجدول | `/mobile/schedule` | جدول أسبوعي مرتب |
| الإشعارات | `/mobile/notifications` | قائمة + وضع مقروء |
| الملف الشخصي | `/mobile/profile` | بيانات + إعدادات |
| تيليجرام | `/mobile/telegram` | ربط + فصل |

### لممثل الدفعة (Representative)
كل ما سبق +:
| الصفحة | الوظيفة |
|--------|---------|
| إنشاء إعلان | `/mobile/announcements/create` |
| إنشاء محاضرة | `/mobile/attendance/lectures/create` |
| تفاصيل محاضرة | عرض QR Code للطلاب + قفل |
| إحصائيات الدفعة | عدد الطلاب + نسبة الحضور |

---

## 🔧 التكوين

### ملف `.env` للتطبيق
```env
APP_NAME="UBMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://localhost

# Mobile uses SQLite
DB_CONNECTION=sqlite
DB_DATABASE=storage/app/native/database.sqlite

# Telegram (optional, for notifications)
TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_USERNAME=

# NativePHP
NATIVEPHP_APP_ID=com.university.ubms
NATIVEPHP_APP_NAME=UBMS
NATIVEPHP_APP_VERSION=1.0.0
NATIVEPHP_MOBILE=true
NATIVEPHP_DESKTOP=false
```

---

## 📲 ميزات Native في التطبيق

### 1. **QR Scanner (تسجيل الحضور)**
- يستخدم الكاميرا الخلفية تلقائياً
- اهتزاز عند النجاح/الفشل
- fallback لإدخال يدوي
- معالجة الأخطاء (انتهاء صلاحية، ازدواجية)

### 2. **الإشعارات**
- إشعارات native عند نشر إعلان جديد
- عدّاد على أيقونة التطبيق (badge)
- صوت + اهتزاز

### 3. **Deep Links**
- `ubms://lecture/{id}` - فتح محاضرة مباشرة
- `ubms://announcement/{id}` - فتح إعلان
- مفيد لإشعارات تيليجرام

### 4. **العمل Offline**
- SQLite محلي يخزّن كل البيانات
- الإعلانات والواجبات متاحة بدون إنترنت
- مزامنة تلقائية عند عودة الاتصال (يحتاج تطوير إضافي)

---

## ⚠️ ملاحظات مهمة

### 1. **NativePHP Mobile في Beta**
- قد توجد أخطاء في الـ runtime
- تحديثات الحزمة قد تكسر compatibility
- لا توجد LTS بعد

### 2. **حجم APK**
- ~80-120 MB (يشمل PHP binary)
- يمكن تقليله بـ ProGuard + R8

### 3. **SQLite vs MySQL**
- بعض ميزات MySQL المتقدمة غير مدعومة
- لكن الأداء ممتاز للتطبيقات الفردية
- التهجرات تعمل بدون تعديل

### 4. **الـ API (للنسخة الويب) لا يزال متاحاً**
- `/api/v1/*` يعمل بالتوازي
- الـ React frontend في `frontend/` ما زال يعمل
- يمكن استخدام نفس قاعدة البيانات

### 5. **Telegram Webhook**
- لا يعمل في التطبيق الـ offline
- البديل: Polling (يسأل البوت عن الرسائل الجديدة)
- أو: Push notifications عبر FCM

---

## 🔨 استكشاف الأخطاء

### مشكلة: الكاميرا لا تعمل
```bash
# تأكد من إذن CAMERA في AndroidManifest
# تحقق من nativephp.config.json
"permissions": ["android.permission.CAMERA"]
```

### مشكلة: قاعدة البيانات فارغة
```bash
# أعد تشغيل التهجرات
php artisan migrate:fresh --seed
```

### مشكلة: التطبيق يفتح شاشة بيضاء
```bash
# امسح الكاش
php artisan optimize:clear
php artisan view:clear
```

### مشكلة: Login لا يعمل
- تحقق من `.env` أن `SESSION_DRIVER=file`
- لا تستخدم `database` session driver مع SQLite قبل التهجرة

---

## 📊 مقارنة: Web vs Mobile

| الميزة | الويب (React) | Mobile (NativePHP) |
|--------|--------------|-------------------|
| الواجهة | React 19 + Shadcn | Blade + TailwindCSS |
| المصادقة | Sanctum Token | Session |
| قاعدة البيانات | MySQL | SQLite |
| الاتصال | متصل دائماً | Offline أولاً |
| الـ QR | getUserMedia | Native Camera API |
| الإشعارات | Web Push | FCM + Native |
| الاهتزاز | محدود | Native |
| التثبيت | PWA (محدود) | Play Store |
| الحجم | 1.3 MB JS | ~100 MB APK |

---

## 🎯 الخطوات التالية المقترحة

1. **اختبر التطبيق محلياً**: شغّل `php artisan serve` وافتح `/mobile/login`
2. **ثبّت Android Studio** وحمّل NDK
3. **شغّل** `php artisan native:install android`
4. **ابنِ APK** بـ `php artisan native:build android --debug`
5. **اختبر على جهاز حقيقي** (أكثر دقة من المحاكي)
6. **سجّل على Play Store** عند الجاهزية

---

## 📞 الدعم

- **NativePHP Docs**: https://nativephp.com/docs
- **Discord**: https://nativephp.com/discord
- **GitHub**: https://github.com/nativephp/laravel

</div>
