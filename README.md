# 🎓 University Batch Management System (UBMS)

<div dir="rtl">

نظام إدارة الدفعات الجامعية - نظام متكامل يربط ممثلي الدفعات بالطلاب لإدارة الإعلانات، الحضور، الواجبات، الجداول، والتواصل عبر تيليجرام.

## ✨ المميزات الرئيسية

### 🔐 المصادقة والصلاحيات
- تسجيل دخول/إنشاء حساب/استعادة كلمة مرور
- أربعة أدوار: مدير عام، مدير كلية، ممثل دفعة، طالب
- حماية عبر Laravel Sanctum + Token-based Auth
- تذكر الجلسة (Remember Me)

### 📢 الإعلانات
- 9 أنواع: عطلة، واجب، محاضرة، جدول، عام، عاجل، طوارئ، اجتماع، مهم
- جدولة الإعلانات للنشر المستقبلي
- تثبيت الإعلانات المهمة
- مرفقات متعددة (PDF, صور, مستندات)
- إحصائيات القراءة لكل إعلان
- بث فوري عبر تيليجرام

### ✅ نظام الحضور
- إنشاء محاضرات مع رمز QR
- QR قابل للانتهاء (افتراضي 15 دقيقة)
- منع التسجيل المزدوج
- 4 حالات: حاضر، غائب، متأخر، بعذر
- قفل التسجيل بعد المحاضرة
- إحصائيات تفصيلية لكل طالب
- تقارير شاملة

### 📚 الواجبات
- إنشاء واجبات بمواعيد نهائية
- مرفقات متعددة
- السماح بالتسليم المتأخر مع نسبة خصم
- إشعار تيليجرام تلقائي

### 📅 الجدول الأسبوعي
- جدولة أسبوعية كاملة
- تفاصيل القاعة، المبنى، المدرس
- عرض مرتب حسب اليوم

### 🤖 تيليجرام
- بوت متكامل للإشعارات
- ربط الحساب عبر رمز تحقق
- بث جماعي لجميع طلاب الدفعة
- إشعارات الإعلانات والواجبات

### 📊 التقارير
- تصدير Excel (مع تنسيق احترافي + RTL)
- تصدير PDF (مع شعار الجامعة + توقيعات)
- تقارير: الحضور، الإعلانات، الواجبات، الطلاب، الإحصائيات

### 🎨 الواجهة
- React 19 + TypeScript + Vite
- TailwindCSS + Shadcn UI
- وضع داكن/فاتح
- دعم كامل للعربية (RTL) والإنجليزية
- تصميم Glassmorphism احترافي
- رسوم متحركة سلسة (Framer Motion)
- رسوم بيانية تفاعلية (Recharts)
- متجاوب بالكامل

## 🏗️ المعمارية

```
ubms/
├── .github/                    # ⭐ تكوين GitHub Actions
│   ├── workflows/
│   │   └── build-android.yml   # بناء APK تلقائياً عبر NativePHP
│   ├── ISSUE_TEMPLATE/         # قوالب البلاغات
│   ├── PULL_REQUEST_TEMPLATE.md
│   └── CODEOWNERS
├── backend/              # Laravel 12 API + NativePHP Mobile
│   ├── app/Http/Controllers/
│   │   ├── Api/          # REST API (للـ React)
│   │   └── MobileController.php  # واجهة Blade للموبايل
│   ├── app/Models/
│   ├── app/Services/
│   │   └── Native/       # خدمات NativePHP (Camera, Notifications)
│   ├── database/migrations/
│   ├── database/seeders/
│   ├── resources/views/mobile/  # صفحات Blade للموبايل
│   ├── config/nativephp.php     # تكوين NativePHP
│   ├── nativephp.config.json    # إعدادات APK
│   └── routes/
│       ├── api.php       # API routes
│       └── mobile.php    # Mobile routes
├── frontend/             # React 19 + Vite (الويب)
│   ├── src/components/   # مكونات UI (Shadcn)
│   ├── src/pages/        # صفحات التطبيق
│   ├── src/lib/          # API client & utils
│   ├── src/store/        # Zustand state
│   └── src/i18n/         # ترجمات
├── database/             # SQL dump
└── docs/                 # التوثيق
```

## 🚀 البدء السريع

### المتطلبات
- PHP 8.3+ مع امتدادات: pdo_mysql, mbstring, gd, zip, curl
- Composer 2+
- MySQL 8.0+ أو MariaDB 10.6+
- Node.js 20+ و npm
- خادم ويب (Apache/Nginx) أو `php artisan serve`

### 1️⃣ إعداد الـ Backend

```bash
cd backend
cp .env.example .env
# عدّل ملف .env: قاعدة البيانات، البريد، تيليجرام
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve  # http://localhost:8000
```

### 2️⃣ إعداد الـ Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev  # http://localhost:5173
```

### 3️⃣ حسابات تجريبية

| الدور | البريد | كلمة المرور |
|------|--------|------------|
| مدير عام | admin@ubms.local | password |
| مدير كلية | college@ubms.local | password |
| ممثل دفعة | rep@ubms.local | password |
| طالب | student1@ubms.local | password |

## 📖 التوثيق

- [دليل التثبيت](./docs/INSTALLATION.md)
- [دليل الإعداد](./docs/CONFIGURATION.md)
- [النشر على الاستضافة المشتركة](./docs/SHARED_HOSTING.md)
- [إعداد بوت تيليجرام](./docs/TELEGRAM_SETUP.md)
- [توثيق API](./docs/API_DOCUMENTATION.md)
- [تصميم قاعدة البيانات](./docs/DATABASE.md)
- [دليل المستخدم (الطالب)](./docs/USER_MANUAL_STUDENT.md)
- [دليل الممثل](./docs/USER_MANUAL_REP.md)
- [دليل المدير](./docs/ADMIN_MANUAL.md)
- [دليل NativePHP Mobile](./docs/NATIVEPHP_GUIDE.md)

## 🤖 GitHub Actions (CI/CD)

يحتوي المشروع على workflow تلقائي لبناء APK عبر NativePHP:

```yaml
.github/workflows/build-android.yml
```

### كيف يعمل؟
1. عند الـ push على `main` أو `master`، يبدأ الـ workflow
2. يثبّت PHP 8.3 + Java 17 + Android SDK
3. يبني الأصول عبر Composer (داخل `backend`) + NPM (داخل `frontend`)
4. يهيّئ NativePHP Mobile
5. يبني APK Debug
6. يرفع الـ APK كـ artifact قابل للتنزيل

### للحصول على الـ APK:
1. ارفع المشروع إلى GitHub
2. اذهب إلى تبويب **Actions**
3. انتظر اكتمال الـ build (5-10 دقائق)
4. نزّل الـ artifact `nativephp-android-app`

## 🔒 الأمان

- CSRF Protection
- XSS Protection ( Blade & React auto-escaping )
- SQL Injection Protection (Eloquent ORM)
- Password Hashing (bcrypt)
- Rate Limiting (60 req/min default, 5 for login)
- Role-based Access Control (Spatie Permission)
- Audit Logs
- Secure File Upload
- Sanctum Token Authentication

## 📄 الترخيص

MIT License - حر للاستخدام والتعديل.

</div>
