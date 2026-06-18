# دليل إعداد بوت تيليجرام - UBMS

<div dir="rtl">

## الخطوة 1: إنشاء البوت

1. افتح تيليجرام وابحث عن **@BotFather**
2. أرسل الأمر `/newbot`
3. اختر اسماً للبوت (مثلاً: "UBMS Bot")
4. اختر معرّفاً فريداً ينتهي بـ `bot` (مثلاً: `my_ubms_bot`)
5. احفظ **HTTP API Token** الذي سيعطيك إياه

مثال على التوكن:
```
1234567890:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
```

## الخطوة 2: الإعداد في النظام

في ملف `backend/.env`:
```env
TELEGRAM_BOT_TOKEN=1234567890:ABC-DEF1234ghIkl-zyx57W2v1u123ew11
TELEGRAM_BOT_USERNAME=my_ubms_bot
TELEGRAM_WEBHOOK_SECRET=any-random-string-here
```

ثم أعد تشغيل الـ backend:
```bash
php artisan config:cache
```

في ملف `frontend/.env`:
```env
VITE_TELEGRAM_BOT_USERNAME=my_ubms_bot
```

## الخطوة 3: تعيين الـ Webhook

### الخيار أ: عبر المتصفح
```
https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://yourdomain.com/telegram/webhook/<SECRET>
```

### الخيار ب: عبر curl
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{
    "url": "https://yourdomain.com/telegram/webhook/<SECRET>",
    "allowed_updates": ["message"]
  }'
```

### الخيار ج: عبر PHP
```bash
php artisan tinker
>>> \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot" . config('services.telegram.bot_token') . "/setWebhook", [
...     'url' => 'https://yourdomain.com/telegram/webhook/' . config('services.telegram.webhook_secret'),
... ]);
```

## الخطوة 4: اختبار الـ Webhook

```bash
# تحقق من حالة الـ webhook
curl "https://api.telegram.org/bot<TOKEN>/getWebhookInfo"
```

النتيجة المتوقعة:
```json
{
  "ok": true,
  "result": {
    "url": "https://yourdomain.com/telegram/webhook/secret",
    "has_custom_certificate": false,
    "pending_update_count": 0
  }
}
```

إذا كان `"pending_update_count"` أكبر من 0، فهناك مشكلة في استقبال الـ webhook.

## الخطوة 5: تجربة الربط

1. سجّل دخولك كطالب أو ممثل في UBMS
2. اذهب إلى صفحة "تيليجرام"
3. اضغط "ربط الحساب"
4. سيظهر رمز تحقق (6 أرقام) و QR Code
5. افتح بوت تيليجرام وابدأ محادثة:
   - أرسل `/start CODE` (مثلاً: `/start 123456`)
   - أو امسح QR Code (سيفتح تيليجرام تلقائياً)
6. ست收到 رسالة تأكيد: "✅ تم ربط حسابك بنجاح"

## كيف يعمل النظام

### ربط الحساب
1. المستخدم يطلب رمزاً من UBMS
2. النظام يولّد رمز 6 أرقام عشوائي ويخزّنه في `users.telegram_verification_code`
3. المستخدم يرسل الرمز للبوت
4. الـ webhook يستقبل الرسالة في `/telegram/webhook/{secret}`
5. `TelegramService::handleWebhook()` يتحقق من الرمز ويربط `chat_id` بالمستخدم

### إرسال الإشعارات
1. ممثل الدفعة ينشر إعلاناً مع تفعيل "إرسال عبر تيليجرام"
2. `TelegramService::broadcastAnnouncement()` يبثّ الرسالة لكل طلاب الدفعة المرتبطين
3. كل رسالة تُسجّل في جدول `telegram_messages` مع الحالة

## أوامر البوت المدعومة

| الأمر | الوظيفة |
|------|--------|
| `/start` | ترحيب + تعليمات |
| `/start CODE` | ربط الحساب بالكود المحدد |
| `123456` (6 أرقام) | ربط الحساب بالكود |

## تخصيص البوت (اختياري)

### تغيير اسم البوت ووصفه
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setMyName" -d "name=UBMS Bot"
curl -X POST "https://api.telegram.org/bot<TOKEN>/setMyDescription" -d "description=نظام إدارة الدفعات الجامعية"
curl -X POST "https://api.telegram.org/bot<TOKEN>/setMyShortDescription" -d "short_description=إشعارات الدفعات الجامعية"
```

### إضافة صورة للبوت
عبر @BotFather:
1. أرسل `/setuserpic`
2. اختر البوت
3. أرسل الصورة

### إضافة قائمة أوامر
```bash
curl -X POST "https://api.telegram.org/bot<TOKEN>/setMyCommands" \
  -H "Content-Type: application/json" \
  -d '{"commands": [{"command": "start", "description": "بدء / ربط الحساب"}, {"command": "help", "description": "المساعدة"}]}'
```

## الأمان

1. **لا تشارك التوكن أبداً** - أي شخص لديه التوكن يتحكم بالبوت
2. **استخدم Webhook Secret** - يمنع الطلبات المزيفة
3. **استخدم HTTPS فقط** - تيليجرام يتطلب HTTPS للـ webhook
4. **Rate Limiting** - تيليجرام يسمح بـ 30 رسالة/ثانية لكل بوت، و 1 رسالة/ثانية لكل مستخدم

## استكشاف الأخطاء

### البوت لا يستجيب
- تحقق من `getWebhookInfo` - هل `pending_update_count` > 0؟
- راجع `storage/logs/laravel.log` للأخطاء
- تأكد أن الـ URL صحيح ويستخدم HTTPS

### الرسائل لا تصل
- تحقق من `users.telegram_chat_id` و `telegram_verified` في قاعدة البيانات
- راجع جدول `telegram_messages` للحالة والأخطاء

### التوكن غير صالح
- أعد إنشائه عبر @BotFather: `/token` → اختر البوت → `Revoke current token`

</div>
