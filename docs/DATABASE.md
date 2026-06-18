# تصميم قاعدة البيانات - UBMS

<div dir="rtl">

## مخطط ER (Entity-Relationship)

```
┌──────────────────┐
│   universities   │
│  - id (PK)       │
│  - name          │
│  - code          │
└────────┬─────────┘
         │ 1:N
┌────────▼─────────┐
│    colleges      │
│  - id (PK)       │
│  - university_id │──┐
│  - name          │  │
└────────┬─────────┘  │
         │ 1:N        │
┌────────▼─────────┐  │
│   departments    │  │
│  - id (PK)       │  │
│  - college_id    │──┘
│  - name          │
└────────┬─────────┘
         │ 1:N
┌────────▼─────────┐
│     levels       │
│  - id (PK)       │
│  - department_id │
│  - level_number  │
└────────┬─────────┘
         │ 1:N
┌────────▼─────────┐
│    sections      │
│  - id (PK)       │
│  - level_id      │
│  - capacity      │
└────────┬─────────┘
         │ 1:N
┌────────▼─────────┐         ┌──────────────────┐
│     batches      │◀────────│   batch_course   │ (M:N)
│  - id (PK)       │         │  - batch_id      │──┐
│  - section_id    │         │  - course_id     │  │
│  - code          │         │  - level_id      │  │
│  - start_year    │         └──────────────────┘  │
└────────┬─────────┘                               │
         │ 1:N                                     │
         ├─── students ────────────────────────────┼──→ users
         ├─── representatives ─────────────────────┤
         ├─── announcements                       │
         ├─── lectures ─── attendances            │
         ├─── assignments                         │
         └─── schedules                           │
                                                  │
                              ┌───────────────────┘
                              ▼
                    ┌──────────────────┐
                    │      courses     │
                    │  - id (PK)       │
                    │  - department_id │
                    │  - code          │
                    │  - name          │
                    └──────────────────┘

┌──────────────────┐         ┌──────────────────────┐
│      users       │         │  site_notifications  │
│  - id (PK)       │◀────────│  - user_id           │
│  - email         │         │  - title             │
│  - password      │         │  - is_read           │
│  - telegram_*    │         └──────────────────────┘
│  - status        │
│  - locale        │         ┌──────────────────────┐
└──────────────────┘         │    audit_logs        │
         │                   │  - user_id           │
         ├── students        │  - action            │
         ├── representatives │  - resource_type     │
         ├── college_admins  │  - old_values        │
         └── super_admins    │  - new_values        │
                              └──────────────────────┘

┌──────────────────┐         ┌──────────────────────┐
│   announcements  │         │ telegram_messages    │
│  - id (PK)       │         │  - id (PK)           │
│  - batch_id      │         │  - chat_id           │
│  - user_id       │         │  - message           │
│  - type          │         │  - status            │
│  - is_pinned     │         └──────────────────────┘
│  - scheduled_at  │
│  - published_at  │         ┌──────────────────────┐
└──────────────────┘         │      settings        │
         │                   │  - key (UNIQUE)      │
         ├── attachments     │  - value             │
         └── reads           │  - group             │
                              └──────────────────────┘

┌──────────────────┐         ┌──────────────────────┐
│     lectures     │         │     assignments      │
│  - id (PK)       │         │  - id (PK)           │
│  - batch_id      │         │  - batch_id          │
│  - course_id     │         │  - course_id         │
│  - qr_token      │         │  - deadline          │
│  - attendance_   │         │  - max_grade         │
│    locked        │         └──────────────────────┘
└────────┬─────────┘                  │
         │ 1:N                        ├── attachments
         ▼                            └── submissions
┌──────────────────┐
│   attendances    │
│  - id (PK)       │
│  - lecture_id    │
│  - student_id    │
│  - status        │
└──────────────────┘
```

## الجداول الرئيسية

### 1. users
المستخدمون بكل الأدوار. حقل `telegram_chat_id` يربط الحساب بـ تيليجرام.

### 2. universities → colleges → departments → levels → sections → batches
هرمية جامعية كاملة. كل مستوى له علاقة بأعلى منه.

### 3. students / representatives / college_admins / super_admins
جداول امتداد (profile tables) لكل دور. تحوي معلومات إضافية خاصة بالدور.

### 4. announcements
الإعلانات. يدعم 9 أنواع، الجدولة، التثبيت، والربط بمقرر.

### 5. lectures + attendances
كل محاضرة لها QR Token فريد. كل سجل حضور يربط طالباً بمحاضرة بحالة معينة. يُمنع التكرار عبر `UNIQUE(lecture_id, student_id)`.

### 6. assignments + attachments
الواجبات مع مرفقات متعددة. يدعم التسليم المتأخر مع خصم.

### 7. schedules + schedule_exceptions
الجدول الأسبوعي مع دعم الاستثناءات (إلغاء/تأجيل/استبدال).

### 8. site_notifications
إشعارات داخل التطبيق لكل مستخدم. يدعم الجدولة.

### 9. telegram_messages
سجل كل رسالة تيليجرام مع الحالة (`queued` | `sent` | `failed`).

### 10. audit_logs
سجل كل العمليات الحساسة (إنشاء/تعديل/حذف/تسجيل دخول). يحوي IP و User Agent.

### 11. download_logs
سجل كل عملية تحميل ملف لأغراض الأمان والإحصاء.

### 12. settings
إعدادات عامة key-value مع تجميع حسب `group`.

## الفهارس (Indexes)

### فهارس الأداء
- `users(email, status)` — تسجيل الدخول
- `users(telegram_chat_id)` — بحث حساب تيليجرام
- `announcements(batch_id, is_published, published_at)` — قائمة الإعلانات
- `announcements(batch_id, type)` — تصفية حسب النوع
- `announcements(scheduled_at)` — النشر المجدول
- `attendances(lecture_id, student_id) UNIQUE` — منع التكرار
- `attendances(lecture_id, status)` — إحصائيات سريعة
- `lectures(batch_id, date)` — عرض المحاضرات
- `lectures(qr_token)` — البحث عن محاضرة بالـ QR
- `site_notifications(user_id, is_read)` — عدّ الإشعارات غير المقروءة

### فهارس علاقات (Foreign Keys)
كل FK له index تلقائي في MySQL.

## العلاقات (Relationships)

| الجدول | يربط بـ | النوع | onDelete |
|--------|--------|------|----------|
| colleges.university_id | universities.id | N:1 | CASCADE |
| departments.college_id | colleges.id | N:1 | CASCADE |
| levels.department_id | departments.id | N:1 | CASCADE |
| sections.level_id | levels.id | N:1 | CASCADE |
| batches.section_id | sections.id | N:1 | CASCADE |
| students.user_id | users.id | N:1 | CASCADE |
| students.batch_id | batches.id | N:1 | CASCADE |
| representatives.user_id | users.id | N:1 | CASCADE |
| announcements.batch_id | batches.id | N:1 | CASCADE |
| announcements.user_id | users.id | N:1 | CASCADE |
| announcements.course_id | courses.id | N:1 | SET NULL |
| lectures.batch_id | batches.id | N:1 | CASCADE |
| lectures.course_id | courses.id | N:1 | CASCADE |
| attendances.lecture_id | lectures.id | N:1 | CASCADE |
| attendances.student_id | users.id | N:1 | CASCADE |
| attendances.recorded_by | users.id | N:1 | SET NULL |
| assignments.batch_id | batches.id | N:1 | CASCADE |
| assignments.course_id | courses.id | N:1 | SET NULL |
| schedules.batch_id | batches.id | N:1 | CASCADE |
| schedules.course_id | courses.id | N:1 | CASCADE |
| site_notifications.user_id | users.id | N:1 | CASCADE |
| audit_logs.user_id | users.id | N:1 | SET NULL |
| download_logs.user_id | users.id | N:1 | CASCADE |
| batch_course.batch_id | batches.id | N:1 | CASCADE |
| batch_course.course_id | courses.id | N:1 | CASCADE |
| batch_course.level_id | levels.id | N:1 | SET NULL |

## الـ Seeders

ملف `DatabaseSeeder.php` ينشئ:
- 1 جامعة + 1 كلية + 1 قسم + 1 مستوى + 1 شعبة + 1 دفعة
- 5 مقررات للقسم
- 1 مدير عام + 1 مدير كلية + 1 ممثل + 30 طالب
- 9 إعلانات تجريبية
- محاضرات وحضور لـ 14 يوم
- 4 واجبات
- جدول أسبوعي كامل

## التهجير (Migrations)

كل التهجرات في `database/migrations/` بترتيب زمني:
1. `0001_create_users_table` — المستخدمون والجلسات
2. `0002_create_university_structure_tables` — الهرمية الجامعية
3. `0003_create_roles_and_profiles_tables` — الأدوار والبروفايلات
4. `0004_create_courses_tables` — المقررات
5. `0005_create_announcements_tables` — الإعلانات
6. `0006_create_attendance_tables` — الحضور
7. `0007_create_assignments_tables` — الواجبات
8. `0008_create_schedules_tables` — الجداول
9. `0009_create_notifications_logs_settings_tables` — الإشعارات والسجلات

</div>
