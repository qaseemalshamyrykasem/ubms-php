# 🤝 Contributing to UBMS

<div dir="rtl">

شكراً لاهتمامك بالمساهمة في UBMS! هذا الدليل يشرح كيف تساهم.

## 🚀 البدء السريع

### 1. Fork المستودع
```bash
# على GitHub، اضغط Fork
git clone https://github.com/YOUR_USERNAME/ubms.git
cd ubms
git remote add upstream https://github.com/ORIGINAL_OWNER/ubms.git
```

### 2. إنشاء فرع للميزة الجديدة
```bash
git checkout -b feature/amazing-feature
# أو للإصلاحات:
git checkout -b fix/issue-123
```

### 3. إعداد بيئة التطوير
```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve

# Frontend (في terminal آخر)
cd frontend
npm install
npm run dev
```

## 📋 معايير الكود

### PHP / Laravel
- اتبع [PSR-12](https://www.php-fig.org/psr/psr-12/)
- استخدم Type hints و Return types
- اكتب DocBlocks للـ methods العامة
- استخدم FormRequests للتحقق
- ضع Business Logic في Services، ليس في Controllers

```php
// ✅ جيد
public function create(Request $request): JsonResponse
{
    $data = $request->validate([...]);
    $announcement = $this->service->create($data);
    return response()->json($announcement, 201);
}

// ❌ سيء
public function create(Request $request)
{
    // logic مباشر في controller
}
```

### TypeScript / React
- استخدم TypeScript دائماً (لا `any`)
- اكتب interfaces لكل البيانات
- اتبع نمط Functional Components + Hooks
- استخدم React Query للـ server state
- استخدم Zustand للـ client state

### Blade / Mobile
- استخدم البنية الهرمية (layouts, partials)
- اكتب CSS في الـ layout الرئيسي فقط
- استخدم classes متناسقة مع الـ design system

## 🔍 قبل الـ Pull Request

### قائمة التحقق
- [ ] الكود يعمل بدون أخطاء
- [ ] تم اختبار الميزة محلياً
- [ ] تم تحديث التوثيق إن لزم
- [ ] لا توجد `console.log` أو `dd()` متبقية
- [ ] تم احترام أسلوب الكود
- [ ] الـ git commit messages واضحة

### صيغة Commit Messages
```
type(scope): description

[optional body]

[optional footer]
```

**أنواع الـ commits:**
- `feat`: ميزة جديدة
- `fix`: إصلاح خطأ
- `docs`: توثيق
- `style`: تنسيق (لا يؤثر على الكود)
- `refactor`: إعادة هيكلة
- `test`: إضافة اختبارات
- `chore`: مهام صيانة

**أمثلة:**
```
feat(attendance): add QR code auto-refresh every 5 minutes
fix(auth): resolve session timeout issue on mobile
docs(api): update authentication endpoints
```

## 🧪 الاختبارات

### Backend
```bash
cd backend
php artisan test
php artisan test --filter=AnnouncementTest
```

### Frontend
```bash
cd frontend
npm run lint
npm run test
```

## 📝 نوع المساهمات المرحّب بها

### ✅ مرحّب بها
- إصلاح الأخطاء
- تحسينات الأداء
- تحسينات الواجهة (UI/UX)
- دعم لغات جديدة
- توثيق أفضل
- اختبارات جديدة
- ميزات جديدة (ناقشها أولاً في Issue)

### ⚠️ تتطلب مناقشة
- تغييرات كسرية (breaking changes)
- إعادة هيكلة كبيرة
- تغيير dependencies رئيسية
- ميزات تتطلب تغيير قاعدة البيانات

## 🐛 الإبلاغ عن الأخطاء

استخدم [Bug Report Template](.github/ISSUE_TEMPLATE/bug_report.md).

### الأخطاء الأمنية
**لا تفتح issue عام للأخطاء الأمنية!** راسلنا مباشرة على: security@ubms.local

## 💡 اقتراح ميزة

استخدم [Feature Request Template](.github/ISSUE_TEMPLATE/feature_request.md).

## 🌍 ترجمة المشروع

لإضافة لغة جديدة:
1. انسخ `lang/ar/` إلى `lang/{code}/`
2. ترجم كل الملفات
3. أضف اللغة في `config/app.php`
4. أضف اللغة في `frontend/src/i18n/config.ts`
5. أرسل PR

## 📞 التواصل

- **Issues**: للاقتراحات والأخطاء
- **Discussions**: للنقاشات العامة
- **Email**: للأسئلة المباشرة

## 📜 Code of Conduct

كن محترماً ومهذباً. نحن نرحب بالجميع بغض النظر عن:
- الخبرة (مبتدئ أو خبير)
- الجنسية أو اللغة
- الخلفية التقنية
- المستوى الأكاديمي

السلوك غير المقبول:
- الشتائم أو الإهانات
- التحرش بأي شكل
- السبام أو الإعلانات
- سرقة الكود دون نسب

---

شكراً لمساهمتك في جعل UBMS أفضل! 🎉

</div>
