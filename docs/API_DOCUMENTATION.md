# توثيق UBMS REST API

<div dir="rtl">

## المعلومات العامة

- **Base URL**: `https://yourdomain.com/api/v1`
- **Authentication**: Bearer Token (Laravel Sanctum)
- **Content-Type**: `application/json` (عدا الرفع: `multipart/form-data`)
- **Language**: `Accept-Language: ar|en`

## المصادقة

### تسجيل الدخول
```http
POST /auth/login
Content-Type: application/json

{
  "email": "rep@ubms.local",
  "password": "password",
  "remember": true
}
```

**Response 200**:
```json
{
  "message": "تم تسجيل الدخول بنجاح.",
  "user": { "id": 3, "name": "...", "role": "representative", "email": "..." },
  "token": "1|abcdef123456..."
}
```

### تسجيل طالب جديد
```http
POST /auth/register
Content-Type: application/json

{
  "name": "Ahmed Ali",
  "name_ar": "أحمد علي",
  "email": "ahmed@student.com",
  "password": "StrongPass123",
  "password_confirmation": "StrongPass123",
  "phone": "+967777123456",
  "batch_id": 1,
  "student_id": "CS-2024-A-100"
}
```

### استعادة كلمة المرور
```http
POST /auth/forgot-password
{ "email": "user@example.com" }

POST /auth/reset-password
{
  "token": "...",
  "email": "user@example.com",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

### الملف الشخصي
```http
GET  /auth/me
PUT  /auth/profile        # تحديث الملف
PUT  /auth/password       # تغيير كلمة المرور
POST /auth/logout
```

## الإعلانات

### قائمة الإعلانات
```http
GET /announcements?per_page=15&type=urgent&q=محاضرة&course_id=2
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "title": "محاضرة غداً",
      "body": "...",
      "type": "lecture",
      "is_pinned": true,
      "is_published": true,
      "published_at": "2025-06-17T10:00:00Z",
      "author": { "id": 3, "name": "Rep Mohammed" },
      "course": { "id": 1, "name_ar": "أنظمة قواعد البيانات" },
      "attachments": [...]
    }
  ],
  "current_page": 1,
  "total": 50
}
```

### إنشاء إعلان
```http
POST /announcements
Content-Type: multipart/form-data
Authorization: Bearer {token}

title=إعلان عاجل
body=نص الإعلان
type=urgent
is_pinned=1
send_telegram=1
course_id=1
scheduled_at=2025-06-18T10:00:00Z   # اختياري
attachments[]=@/path/to/file.pdf
```

### تفاصيل إعلان
```http
GET /announcements/{id}
```

### إحصائيات القراءة
```http
GET /announcements/{id}/stats
```

```json
{
  "total": 30,
  "read": 25,
  "unread": 5,
  "rate": 83.3
}
```

### تثبيت/إلغاء تثبيت
```http
POST /announcements/{id}/pin
```

### حذف
```http
DELETE /announcements/{id}
```

## الحضور

### قائمة المحاضرات
```http
GET /attendance/lectures?course_id=1&from=2025-06-01&to=2025-06-30
```

### إنشاء محاضرة
```http
POST /attendance/lectures
{
  "course_id": 1,
  "title": "محاضرة قواعد البيانات 5",
  "date": "2025-06-18",
  "start_time": "09:00",
  "end_time": "10:30",
  "room": "305"
}
```

### توليد QR Code
```http
GET /attendance/lectures/{id}/qr
Returns: image/svg+xml
```

### تحديث QR (لإعادة الضبط)
```http
POST /attendance/lectures/{id}/refresh-qr
```

### تسجيل الحضور اليدوي (ممثل)
```http
POST /attendance/lectures/{id}/submit
{
  "records": [
    { "student_id": 5, "status": "present" },
    { "student_id": 6, "status": "absent", "notes": "لم يحضر" },
    { "student_id": 7, "status": "late" },
    { "student_id": 8, "status": "excused", "notes": "عذر طبي" }
  ]
}
```

### مسح QR (طالب)
```http
POST /attendance/scan
{
  "lecture_id": 12,
  "token": "550e8400-e29b-41d4-a716-446655440000"
}
```

### قفل التسجيل
```http
POST /attendance/lectures/{id}/lock
```

### إحصائياتي (طالب)
```http
GET /attendance/my-stats
```

```json
{
  "total_lectures": 20,
  "present": 15,
  "late": 2,
  "absent": 2,
  "excused": 1,
  "rate": 85.0,
  "by_course": [...]
}
```

### إحصائيات الدفعة (ممثل)
```http
GET /attendance/batch-stats
```

### سجل الحضور (طالب)
```http
GET /attendance/my-history?from=2025-06-01&to=2025-06-30&course_id=1
```

## الواجبات

### قائمة الواجبات
```http
GET /assignments
```

### إنشاء واجب
```http
POST /assignments
Content-Type: multipart/form-data

title=تصميم قاعدة بيانات
description=تصميم ER diagram لنظام مكتبة
course_id=1
deadline=2025-06-25T23:59:00
max_grade=100
allow_late_submission=1
late_penalty_percent=10
notify_telegram=1
attachments[]=@assignment.pdf
```

### تحميل مرفق
```http
GET /assignments/{id}/attachments/{attachmentId}/download
Returns: application/octet-stream
```

## المقررات

```http
GET    /courses
GET    /courses/{id}
POST   /courses             # college_admin/super_admin
PUT    /courses/{id}
DELETE /courses/{id}
POST   /courses/{id}/files  # رفع ملف
GET    /courses/{id}/files/{fileId}/download
```

## الجدول الأسبوعي

```http
GET    /schedules
POST   /schedules
PUT    /schedules/{id}
DELETE /schedules/{id}
```

## الإشعارات

```http
GET  /notifications
GET  /notifications/unread-count
POST /notifications/{id}/read
POST /notifications/read-all
DELETE /notifications/{id}
DELETE /notifications            # مسح الكل
```

## تيليجرام

```http
GET  /telegram/status
POST /telegram/generate-code    # يولّد رمز + deep link
POST /telegram/disconnect
POST /telegram/test             # إرسال رسالة تجريبية
POST /telegram/webhook          # webhook من تيليجرام
```

## التقارير

```http
GET /reports/attendance/{batchId}/excel?from=YYYY-MM-DD&to=YYYY-MM-DD&course_id=ID
GET /reports/attendance/{batchId}/pdf
GET /reports/students/{batchId}              # Excel
GET /reports/announcements/{batchId}          # Excel
GET /reports/assignments/{batchId}            # Excel
GET /reports/statistics/{batchId}/excel       # Excel
GET /reports/stats/{batchId}                  # JSON
```

## الهيكل الجامعي

```http
GET /structure/hierarchy       # الشجرة الكاملة حسب الصلاحية
GET /universities
GET /colleges
GET /departments?college_id=ID
GET /levels?department_id=ID
GET /sections?level_id=ID
GET /batches?section_id=ID
POST /batches                  # إنشاء دفعة
GET /batches/{id}
GET /batches/{id}/students     # قائمة طلاب الدفعة
```

## لوحة التحكم

```http
GET /dashboard/stats           # إحصائيات حسب الدور
GET /search?q=keyword          # بحث شامل
```

## أكواد الحالة

| الكود | المعنى |
|------|--------|
| 200 | نجاح |
| 201 | تم الإنشاء |
| 204 | نجاح بدون محتوى |
| 400 | طلب غير صالح |
| 401 | غير مصرّح |
| 403 | ممنوع |
| 404 | غير موجود |
| 422 | خطأ في التحقق |
| 429 | تجاوز حد الطلبات |
| 500 | خطأ خادم |

## Postman Collection

ملف JSON كامل متوفر في `docs/ubms-postman.json` لاستيراده في Postman.

## أمثلة بأوامر curl

```bash
# تسجيل الدخول
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"rep@ubms.local","password":"password"}'

# إنشاء إعلان
curl -X POST http://localhost:8000/api/v1/announcements \
  -H "Authorization: Bearer {TOKEN}" \
  -F "title=إعلان جديد" \
  -F "body=محتوى الإعلان" \
  -F "type=general" \
  -F "is_pinned=1"

# تحميل تقرير
curl -X GET http://localhost:8000/api/v1/reports/attendance/1/excel \
  -H "Authorization: Bearer {TOKEN}" \
  -o attendance.xlsx
```

</div>
