# دليل المدير - UBMS

<div dir="rtl">

## أدوار المديرين

### مدير عام (Super Admin)
- إدارة كامل النظام
- إنشاء جامعات وكليات وأقسام
- إدارة كل المستخدمين
- عرض كل التقارير
- إعداد الإعدادات العامة

### مدير كلية (College Admin)
- إدارة كليته فقط
- إنشاء أقسام ومستويات وشُعب ودفعات
- تعيين ممثلي الدفعات
- عرض تقارير كليته

## لوحة التحكم

### مدير عام
- إجمالي الجامعات، الكليات، الدفعات، الطلاب
- إجمالي المستخدمين، الإعلانات، الواجبات، المحاضرات

### مدير كلية
- عدد الدفعات في كليته
- عدد الطلاب
- عدد الأقسام

## إدارة الهيكل الجامعي

### من صفحة "الهيكل"
- عرض الشجرة الكاملة: جامعة → كلية → قسم → مستوى → شعبة → دفعة
- كل مستوى يعرض عدد الدفعات

### إنشاء دفعة جديدة
1. من "الدفعات" → "+"
2. اختر الشعبة
3. أدخل اسم الدفعة (عربي/إنجليزي)
4. الكود (اختياري - يُولّد تلقائياً)
5. سنة البداية والنهاية
6. تاريخ البداية والنهاية (اختياري)
7. اضغط "حفظ"

## إدارة الطلاب

### من "الطلاب"
- عرض قائمة الطلاب في كل دفعة
- لكل طالب: الاسم، الرقم، البريد، الهاتف، حالة تيليجرام

### إضافة طالب لدفعة
1. من تسجيل الطالب (لو سجل نفسه بدون دفعة)
2. أو من "الطلاب" → اختر دفعة → "+"
3. أدخل بيانات الطالب
4. اربطه بدفعة

## تعيين ممثل دفعة

1. اختر الطالب من قائمة الطلاب
2. اضغط "تعيين كممثل"
3. أو من cPanel قاعدة البيانات:
   ```sql
   INSERT INTO representatives (user_id, batch_id, appointed_at, is_active)
   VALUES (5, 1, CURDATE(), 1);
   UPDATE users SET role = 'representative' WHERE id = 5;
   ```

## التقارير

### التقارير المتاحة
- تقرير الحضور (Excel/PDF) لكل دفعة
- قائمة الطلاب (Excel)
- تقرير الإعلانات (Excel)
- تقرير الواجبات (Excel)
- تقرير الإحصائيات (Excel)

### استخدام الفلاتر
- اختر الدفعة
- للتقرير المحدد: اختر المقرر ونطاق التاريخ
- اضغط على بطاقة التقرير للتنزيل

## الإعدادات العامة

### من قاعدة البيانات (جدول `settings`)
- `university.name` — اسم الجامعة
- `app.locale` — اللغة الافتراضية
- `app.dark_mode_default` — الوضع الداكن الافتراضي
- `uploads.max_size_mb` — حد الرفع
- `telegram.enabled` — تفعيل تيليجرام
- `attendance.qr_ttl_minutes` — مدة صلاحية QR
- `attendance.late_threshold_minutes` — حد التأخر

## الأمان

### مراقبة العمليات
- جدول `audit_logs` يسجل كل عملية
- راجعه دورياً للكشف عن أي أنشطة مشبوهة

### إدارة المستخدمين
- حظّر المستخدمين المخالفين: `users.status = 'suspended'`
- لا تحذف المستخدمين (استخدم soft delete)

### النسخ الاحتياطي
- أجرِ نسخاً احتياطياً يومياً لقاعدة البيانات
- احتفظ بنسخ أسبوعية على الأقل

## صيانة النظام

### أوامر مفيدة
```bash
# مسح الكاش
php artisan optimize:clear

# إعادة بناء الكاش
php artisan optimize

# تشغيل التهجرات
php artisan migrate

# إعادة تشغيل الـ seeders
php artisan migrate:fresh --seed

# نشر الإعلانات المجدولة (cron)
php artisan schedule:run
```

### مراقبة الأداء
- راجع `storage/logs/laravel.log` للتحذيرات
- افحص `telegram_messages` للرسائل الفاشلة
- راجع `audit_logs` للنشاط المشبوه

## إعداد Cron Jobs

في cPanel → "Cron Jobs":
```
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```

في `routes/console.php`:
```php
use App\Services\AnnouncementService;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    app(AnnouncementService::class)->publishScheduled();
})->everyMinute();

Schedule::call(function () {
    // نظافة الإشعارات القديمة (أكثر من 90 يوماً)
    \App\Models\SiteNotification::where('created_at', '<', now()->subDays(90))->delete();
})->daily();
```

## الترقيات

### عند إصدار نسخة جديدة
1. **انسخ احتياطياً** قاعدة البيانات والملفات
2. اسحب التحديثات: `git pull`
3. ثبّت الاعتماديات: `composer install --no-dev`
4. شغّل التهجرات: `php artisan migrate --force`
5. أعد بناء الكاش: `php artisan optimize`
6. اختبر النظام

## حل المشكلات

### المستخدمون لا يستطيعون تسجيل الدخول
1. تحقق من `users.status = 'active'`
2. تحقق من `email_verified_at` ليس NULL
3. أعد تعيين كلمة المرور يدوياً:
   ```bash
   php artisan tinker
   >>> $u = User::find(5); $u->password = bcrypt('newpass'); $u->save();
   ```

### تيليجرام لا يعمل
1. تحقق من `TELEGRAM_BOT_TOKEN` في `.env`
2. تحقق من حالة الـ webhook: `https://api.telegram.org/bot{TOKEN}/getWebhookInfo`
3. أعد تعيين الـ webhook إذا لزم

### ملفات الرفع لا تظهر
1. `php artisan storage:link`
2. تحقق من صلاحيات `storage/app/public/`

</div>
