# دليل الإعداد - UBMS

<div dir="rtl">

## متغيرات البيئة الكاملة (Backend)

### التطبيق العام
```env
APP_NAME="University Batch Management System"
APP_ENV=local                    # local | production
APP_KEY=                         # يتم توليده تلقائياً
APP_DEBUG=true                   # false في الإنتاج
APP_TIMEZONE=Asia/Aden
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
```

### قاعدة البيانات
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ubms
DB_USERNAME=root
DB_PASSWORD=
```

### الجلسات والكاش
```env
SESSION_DRIVER=database          # file | database | redis
CACHE_STORE=database             # file | database | redis
QUEUE_CONNECTION=database        # sync | database | redis
```

### البريد الإلكتروني
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### تيليجرام (اختياري)
```env
TELEGRAM_BOT_TOKEN=123456:ABC-DEF...    # من @BotFather
TELEGRAM_BOT_USERNAME=my_ubms_bot       # بدون @
TELEGRAM_WEBHOOK_SECRET=random-string   # لتأمين الـ webhook
```

### رفع الملفات
```env
FILESYSTEM_DISK=public
MAX_UPLOAD_SIZE_MB=20
```

### Sanctum (لـ SPA)
```env
SANCTUM_STATEFUL_DOMAINS=localhost:5173,yourdomain.com
```

## متغيرات الـ Frontend

```env
VITE_API_URL=http://localhost:8000/api/v1
VITE_APP_NAME=UBMS
VITE_TELEGRAM_BOT_USERNAME=my_ubms_bot
```

## الإعدادات المتقدمة

### 1. تغيير حجم الرفع الأقصى
في `config/ubms.php`:
```php
'uploads_max_size_mb' => env('MAX_UPLOAD_SIZE_MB', 20),
```

في `php.ini`:
```ini
upload_max_filesize = 25M
post_max_size = 30M
```

### 2. إعداد Rate Limiting
في `config/security.php`:
```php
'paths' => [
    'auth/login' => ['max_attempts' => 5, 'decay_minutes' => 1],
    'auth/forgot-password' => ['max_attempts' => 3, 'decay_minutes' => 5],
],
```

### 3. تخصيص اللغات
- العربية: `lang/ar/`
- الإنجليزية: `lang/en/`

### 4. تغيير مفتاح التشفير للـ QR
في `app/Services/AttendanceService.php`:
```php
$data['qr_token'] = Str::uuid()->toString();  // افتراضي
```

### 5. تخصيص مدة صلاحية QR
```env
ATTENDANCE_QR_TTL_MINUTES=15
```

### 6. تخصيص حد التأخر
```env
ATTENDANCE_LATE_THRESHOLD_MINUTES=15
```

## تخصيص الشعار والألوان

### شعار الجامعة
1. ضع الصورة في `storage/app/public/logos/`
2. عدّل في قاعدة البيانات: `universities.logo = logos/my-logo.png`

### ألوان الواجهة
في `frontend/src/index.css`:
```css
:root {
  --primary: 243 75% 59%;  /* HSL */
}
.dark {
  --primary: 243 75% 65%;
}
```

## النسخ الاحتياطي

### قاعدة البيانات
```bash
mysqldump -u root -p ubms > backup_$(date +%Y%m%d).sql
```

### ملفات الرفع
```bash
tar -czf storage_backup.tar.gz backend/storage/app/public/
```

### الاستعادة
```bash
mysql -u root -p ubms < backup_20250617.sql
tar -xzf storage_backup.tar.gz -C backend/
```

</div>
