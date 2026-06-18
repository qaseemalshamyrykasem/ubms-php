# 🛡️ Security Policy

<div dir="rtl">

## الأخطاء الأمنية المدعومة

نأخذ الأمان في UBMS بجدية. إذا اكتشفت ثغرة أمنية، يرجى اتباع الخطوات التالية.

## 🚨 الإبلاغ عن خطأ أمني

**يرجى عدم فتح issue عام للأخطاء الأمنية.**

بدلاً من ذلك، راسلنا مباشرة على:
- 📧 البريد: security@ubms.local
- 🔐 PGP: [security.pub.asc](./security.pub.asc)

### معلومات مطلوبة في البلاغ:
1. وصف الخطأ ونوعه
2. خطوات إعادة الإنتاج
3. تأثير الخطأ المحتمل
4. اقتراحات للإصلاح (إن وجدت)
5. إصدار النظام المتأثر

## ⏱️ وقت الاستجابة

| المرحلة | المدة |
|---------|------|
| تأكيد الاستلام | 48 ساعة |
| التقييم الأولي | 7 أيام |
| الإصلاح (حسب الخطورة) | 30-90 يوم |
| الإفصاح العام | بعد الإصلاح بـ 14 يوم |

## 🎖️ الاعتراف

نُصدر تقديراً علنياً للباحثين الأمنيين الذين ساعدونا (بعد إذنهم).

## 🔒 الإصدارات المدعومة

| الإصدار | الدعم الأمني |
|---------|--------------|
| 1.x | ✅ مدعوم |
| < 1.0 | ❌ غير مدعوم |

## 🛡️ ممارسات الأمان في UBMS

### ما نقوم به:
- **Password Hashing**: bcrypt مع cost factor 12
- **CSRF Tokens**: على كل النماذج
- **SQL Injection Protection**: Eloquent ORM + prepared statements
- **XSS Protection**: auto-escaping في Blade + React
- **Rate Limiting**: 60 req/min عام، 5 req/min للـ login
- **File Upload Validation**: أنواع وأحجام محددة
- **Secure Downloads**: عبر Laravel Storage، ليس روابط مباشرة
- **Audit Logs**: كل العمليات الحساسة مسجّلة
- **Role-based Access**: Spatie Permission
- **Token Authentication**: Laravel Sanctum للـ API

### ما يجب على المضيف فعله:
- تفعيل HTTPS فقط
- تحديث PHP و Laravel بانتظام
- نسخ احتياطي دوري لقاعدة البيانات
- مراقبة الـ logs
- استخدام firewall (fail2ban)

## 🔍 نطاق البرنامج الأمني

### ✅ ضمن النطاق:
- ثغرات XSS, CSRF, SQL Injection
- تجاوز الصلاحيات (privilege escalation)
- تسريب البيانات
- أخطاء المصادقة
- File upload vulnerabilities
- Insecure direct object references

### ❌ خارج النطاق:
- هجمات DoS/DDoS
- Clickjacking (نتعامل معها عبر X-Frame-Options)
- Brute force (لدينا rate limiting)
- ثغرات في dependencies (أبلغ عنها مباشرة للحزمة)
- Self-XSS
- أخطاء في التكوين على خوادم المستخدم

## 📚 موارد إضافية

- [OWASP Top 10](https://owasp.org/Top10/)
- [Laravel Security Docs](https://laravel.com/docs/security)
- [PHP Security Guide](https://php.net/manual/en/security.php)

</div>
