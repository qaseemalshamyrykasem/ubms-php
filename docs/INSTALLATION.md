# دليل التثبيت الكامل - UBMS

<div dir="rtl">

## المتطلبات الأساسية

| المكون | الإصدار الأدنى | الموصى به |
|-------|---------------|----------|
| PHP | 8.3 | 8.3+ |
| Composer | 2.6 | 2.7+ |
| MySQL | 8.0 | 8.0+ |
| Node.js | 20 LTS | 22 LTS |
| npm | 10 | 11+ |

### امتدادات PHP المطلوبة
```
pdo_mysql, mbstring, gd, zip, curl, xml, bcmath, intl, opcache
```

## الخطوة 1: تثبيت الـ Backend

```bash
# 1. انتقل لمجلد الـ backend
cd backend

# 2. ثبّت اعتماديات PHP
composer install --optimize-autoloader --no-dev

# 3. انسخ ملف الإعداد
cp .env.example .env

# 4. ولّد مفتاح التطبيق
php artisan key:generate

# 5. عدّل ملف .env (راجع CONFIGURATION.md)
#  - DB_DATABASE, DB_USERNAME, DB_PASSWORD
#  - APP_URL
#  - TELEGRAM_BOT_TOKEN (اختياري)

# 6. أنشئ قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE ubms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. شغّل التهجرات والـ seeders
php artisan migrate --seed

# 8. أنشئ رابط التخزين
php artisan storage:link

# 9. شغّل الخادم (للتطوير)
php artisan serve
```

## الخطوة 2: تثبيت الـ Frontend

```bash
# 1. انتقل لمجلد الـ frontend
cd ../frontend

# 2. انسخ ملف الإعداد
cp .env.example .env

# 3. عدّل VITE_API_URL إذا لزم
#   VITE_API_URL=http://localhost:8000/api/v1

# 4. ثبّت الحزم
npm install

# 5. شغّل خادم التطوير
npm run dev
```

افتح المتصفح على: http://localhost:5173

## الخطوة 3: بناء الإنتاج

```bash
# Backend
cd backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Frontend
cd ../frontend
npm run build
# الناتج في مجلد dist/
```

## التحقق من التثبيت

1. افتح `http://localhost:8000/api/v1/auth/login` في المتصفح — يجب أن يظهر خطأ 405 (لأن GET غير مدعوم، وهذا طبيعي)
2. افتح `http://localhost:5173` — يجب أن تظهر صفحة تسجيل الدخول
3. جرّب الدخول بـ `admin@ubms.local` / `password`

## مشاكل شائعة

### 1. خطأ "No application encryption key has been specified"
```bash
php artisan key:generate
```

### 2. خطأ في الاتصال بقاعدة البيانات
- تحقق من بيانات `DB_*` في `.env`
- تأكد أن خدمة MySQL تعمل
- تأكد أن المستخدم لديه صلاحيات على قاعدة البيانات

### 3. خطأ CORS من الـ Frontend
- أضف رابط الـ frontend إلى `SANCTUM_STATEFUL_DOMAINS` في `.env` للـ backend
- تحقق من `config/cors.php`

### 4. ملفات التحميل لا تظهر
```bash
php artisan storage:link
```

### 5. تيليجرام لا يعمل
- تأكد من `TELEGRAM_BOT_TOKEN` في `.env`
- أنشئ بوت جديد عبر [@BotFather](https://t.me/BotFather)
- اضبط Webhook: `php artisan telegram:set-webhook`

## الخطوة التالية

- [إعداد تيليجرام بوت](./TELEGRAM_SETUP.md)
- [النشر على استضافة مشتركة](./SHARED_HOSTING.md)

</div>
