# دليل النشر على الاستضافة المشتركة (cPanel)

<div dir="rtl">

## المتطلبات على cPanel

- PHP 8.3+ (فعّل امتدادات: pdo_mysql, mbstring, gd, zip, curl, intl)
- MySQL 8.0+
- الوصول إلى Terminal (موصى به) أو File Manager
- SSL مفعّل (مجاني عبر Let's Encrypt في cPanel)

## الخطوة 1: رفع الملفات

### عبر File Manager
1. اضغط "Online File Manager" في cPanel
2. اذهب إلى `public_html/`
3. ارفع ملف ZIP الخاص بـ UBMS
4. فك الضغط

### البنية النهائية
```
public_html/
├── backend/           # كل ملفات Laravel
│   ├── app/
│   ├── public/        # محتوى هذا المجلد يُنسخ لـ public_html
│   ├── storage/
│   ├── .env
│   └── ...
├── frontend/dist/     # ملفات React المبنية
├── index.php          # مخصص لخدمة الـ Frontend
└── .htaccess
```

## الخطوة 2: إعداد قاعدة البيانات

1. في cPanel → "MySQL Database Wizard"
2. أنشئ قاعدة بيانات: `youruser_ubms`
3. أنشئ مستخدم: `youruser_ubmsuser` + كلمة مرور قوية
4. اربط المستخدم بقاعدة البيانات بكل الصلاحيات
5. استورد ملف `database/ubms_schema.sql` عبر phpMyAdmin

## الخطوة 3: إعداد ملف .env

أنشئ ملف `backend/.env`:

```env
APP_NAME="UBMS"
APP_ENV=production
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxx
APP_DEBUG=false
APP_TIMEZONE=Asia/Aden
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=youruser_ubms
DB_USERNAME=youruser_ubmsuser
DB_PASSWORD=StrongPassword123!

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=public

APP_LOCALE=ar

TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username
TELEGRAM_WEBHOOK_SECRET=random_string_here

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="UBMS"
```

## الخطوة 4: نقل محتوى public/

```bash
# انسخ كل محتويات backend/public/ إلى public_html/
mv backend/public/* public_html/
mv backend/public/.htaccess public_html/

# عدّل index.php في public_html/
```

عدّل `public_html/index.php`:
```php
<?php
// الاتجاه إلى backend بدلاً من المسار النسبي
require __DIR__.'/../backend/vendor/autoload.php';
$app = require_once __DIR__.'/../backend/bootstrap/app.php';
$app->handleRequest(Illuminate\Http\Request::capture());
```

## الخطوة 5: ضبط الصلاحيات

```bash
chmod -R 755 backend/
chmod -R 775 backend/storage/
chmod -R 775 backend/bootstrap/cache/
```

## الخطوة 6: تحسين الأداء

```bash
cd backend
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## الخطوة 7: إعداد الواجهة الأمامية

محلياً (لأن npm قد لا يكون متاحاً على cPanel):

```bash
cd frontend
cp .env.example .env
# عدّل VITE_API_URL=https://yourdomain.com/api/v1
npm install
npm run build
```

ثم ارفع مجلد `dist/` إلى `public_html/`.

أنشئ `public_html/.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Serve React app
    RewriteCond %{REQUEST_URI} !^/api
    RewriteCond %{REQUEST_URI} !^/storage
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.html [L]
    
    # Proxy API requests to Laravel
    RewriteRule ^api/(.*)$ backend/public/index.php [L]
</IfModule>

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"

# Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json
</IfModule>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
    ExpiresByType text/css "access plus 1 week"
    ExpiresByType application/javascript "access plus 1 week"
</IfModule>
```

## الخطوة 8: تعيين Webhook لـ تيليجرام

```bash
# محلياً عبر curl:
curl -X POST "https://api.telegram.org/bot{TOKEN}/setWebhook" \
  -d "url=https://yourdomain.com/telegram/webhook/{WEBHOOK_SECRET}" \
  -d "allowed_updates=[\"message\"]"
```

## الخطوة 9: إعداد Cron Job

في cPanel → "Cron Jobs":
```
* * * * * cd /home/youruser/public_html/backend && php artisan schedule:run >> /dev/null 2>&1
```

أضف في `app/Console/Kernel.php` أو `routes/console.php`:
```php
use App\Services\AnnouncementService;
Schedule::call(function () {
    app(AnnouncementService::class)->publishScheduled();
})->everyMinute();
```

## الخطوة 10: اختبار

1. افتح `https://yourdomain.com` → صفحة تسجيل الدخول
2. جرّب `admin@ubms.local` / `password` (ولكن غيّرها فوراً!)
3. اختبر رفع ملف
4. اختبر تسجيل طالب جديد

## تحديث النسخة لاحقاً

```bash
cd backend
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## مشاكل شائعة على cPanel

### 1. 500 Internal Server Error
- تحقق من `error_log` في cPanel
- غالباً بسبب صلاحيات الملفات أو إعدادات PHP

### 2. الـ Frontend لا يصل لـ API
- تأكد من `VITE_API_URL` الصحيح قبل البناء
- تحقق من إعدادات CORS في `config/cors.php`

### 3. لم تعمل storage:link
في cPanel، قد تحتاج إنشاء symlink يدوياً:
```bash
ln -s /home/youruser/public_html/backend/storage/app/public /home/youruser/public_html/storage
```

### 4. رفع الملفات الكبيرة يفشل
في cPanel → "MultiPHP INI Editor":
```ini
upload_max_filesize = 30M
post_max_size = 35M
max_execution_time = 300
memory_limit = 256M
```

</div>
