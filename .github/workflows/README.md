# GitHub Actions - Build NativePHP Android APK

<div dir="rtl">

## 📱 وصف الـ Workflow

ملف `build-android.yml` ينفّذ تلقائياً عند الـ push على فرع `main` أو `master`. يقوم بـ:

1. **Checkout** الكود من المستودع
2. **إعداد PHP 8.2** مع امتدادات: dom, curl, libxml, mbstring, zip, pdo_sqlite
3. **إعداد Java JDK 17** (Zulu distribution)
4. **إعداد Android SDK**
5. **تثبيت اعتماديات Composer** (Laravel + NativePHP)
6. **تثبيت NPM + بناء الأصول** بـ mode=android
7. **تهيئة NativePHP Mobile** عبر `php artisan native:install --force`
8. **بناء APK Debug** عبر `php artisan native:package android --build-type=debug`
9. **رفع الـ APK** كـ artifact قابل للتنزيل

## 🚀 الاستخدام

### 1. ارفع المشروع إلى GitHub
```bash
git init
git add .
git commit -m "Initial UBMS project with NativePHP mobile support"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/ubms.git
git push -u origin main
```

### 2. انتظر اكتمال الـ Build
- اذهب إلى تبويب **Actions** في مستودعك على GitHub
- ستجد الـ workflow يعمل تلقائياً
- يستغرق عادةً 5-10 دقائق

### 3. نزّل الـ APK
- عند نجاح الـ build، اضغط على الـ run
- في الأسفل، قسم **Artifacts**
- اضغط على `nativephp-android-app` للتنزيل
- سيُنزّل ملف ZIP يحتوي على `app-debug.apk`

## ⚙️ متطلبات إضافية (اختياري)

### لتسريع الـ build:
أضف caching في الـ workflow:
```yaml
- name: Cache Composer packages
  uses: actions/cache@v4
  with:
    path: vendor
    key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
```

### لـ Release builds (APK موقّع):
1. أنشئ keystore محلياً:
   ```bash
   keytool -genkey -v -keystore ubms.keystore -alias ubms -keyalg RSA -keysize 2048 -validity 10000
   ```
2. أضف الـ keystore كـ GitHub Secret باسم `KEYSTORE_BASE64`
3. عدّل الـ workflow لتوقيع الـ APK

## 🔍 استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| فشل `native:install` | تأكد من تثبيت `nativephp/mobile` في composer.json |
| فشل Android SDK | تأكد من `android-actions/setup-android@v3` |
| نفاذ مساحة القرص | أضف `df -h` قبل/بعد الخطوات للتشخيص |
| timeout | ارفع `timeout-minutes` في job (افتراضي 360) |

</div>
