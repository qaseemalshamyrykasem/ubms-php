# بنية المشروع - UBMS

<div dir="rtl">

## البنية الكاملة

```
ubms/
├── backend/                          # Laravel 12 API
│   ├── app/
│   │   ├── Console/
│   │   │   └── Commands/             # أوامر CLI مخصصة
│   │   ├── Exports/                  # تصدير Excel
│   │   │   ├── AttendanceExport.php
│   │   │   ├── AnnouncementsExport.php
│   │   │   ├── AssignmentsExport.php
│   │   │   ├── StatisticsExport.php
│   │   │   └── StudentsExport.php
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── Api/              # كل controllers API
│   │   │   │       ├── AuthController.php
│   │   │   │       ├── AnnouncementController.php
│   │   │   │       ├── AttendanceController.php
│   │   │   │       ├── AssignmentController.php
│   │   │   │       ├── CourseController.php
│   │   │   │       ├── DashboardController.php
│   │   │   │       ├── NotificationController.php
│   │   │   │       ├── ReportController.php
│   │   │   │       ├── ScheduleController.php
│   │   │   │       ├── StructureController.php
│   │   │   │       └── TelegramController.php
│   │   │   ├── Middleware/
│   │   │   │   └── AuditLogMiddleware.php
│   │   │   └── Requests/             # Form Requests
│   │   │       ├── Auth/
│   │   │       └── Announcement/
│   │   ├── Imports/                  # استيراد Excel
│   │   │   └── StudentsImport.php
│   │   ├── Models/                   # Eloquent Models
│   │   │   ├── Announcement.php
│   │   │   ├── AnnouncementAttachment.php
│   │   │   ├── AnnouncementRead.php
│   │   │   ├── AnnouncementTemplate.php
│   │   │   ├── Assignment.php
│   │   │   ├── AssignmentAttachment.php
│   │   │   ├── AssignmentDownload.php
│   │   │   ├── AssignmentSubmission.php
│   │   │   ├── Attendance.php
│   │   │   ├── AttendanceExcuse.php
│   │   │   ├── AuditLog.php
│   │   │   ├── Batch.php
│   │   │   ├── College.php
│   │   │   ├── CollegeAdmin.php
│   │   │   ├── Course.php
│   │   │   ├── CourseFile.php
│   │   │   ├── Department.php
│   │   │   ├── DownloadLog.php
│   │   │   ├── Lecture.php
│   │   │   ├── Level.php
│   │   │   ├── Representative.php
│   │   │   ├── Schedule.php
│   │   │   ├── ScheduleException.php
│   │   │   ├── Section.php
│   │   │   ├── Setting.php
│   │   │   ├── SiteNotification.php
│   │   │   ├── Student.php
│   │   │   ├── SuperAdmin.php
│   │   │   ├── TelegramMessage.php
│   │   │   ├── University.php
│   │   │   └── User.php
│   │   ├── Policies/                 # Authorization Policies
│   │   │   ├── AnnouncementPolicy.php
│   │   │   ├── AssignmentPolicy.php
│   │   │   ├── BatchPolicy.php
│   │   │   ├── LecturePolicy.php
│   │   │   └── SchedulePolicy.php
│   │   └── Services/                 # Business Logic
│   │       ├── AnnouncementService.php
│   │       ├── AssignmentService.php
│   │       ├── AttendanceService.php
│   │       ├── ReportService.php
│   │       ├── TelegramService.php
│   │       └── UniversityService.php
│   ├── bootstrap/
│   │   └── app.php                   # Bootstrap configuration
│   ├── config/                       # إعدادات Laravel
│   │   ├── app.php
│   │   ├── auth.php
│   │   ├── cache.php
│   │   ├── cors.php
│   │   ├── database.php
│   │   ├── filesystems.php
│   │   ├── permission.php            # Spatie Permission
│   │   ├── queue.php
│   │   ├── sanctum.php
│   │   ├── security.php              # إعدادات الأمان
│   │   ├── services.php              # خدمات خارجية (تيليجرام)
│   │   ├── session.php
│   │   └── ubms.php                  # إعدادات UBMS
│   ├── database/
│   │   ├── factories/
│   │   │   └── UserFactory.php
│   │   ├── migrations/               # 9 ملفات migration
│   │   │   ├── 0001_create_users_table.php
│   │   │   ├── 0002_create_university_structure_tables.php
│   │   │   ├── 0003_create_roles_and_profiles_tables.php
│   │   │   ├── 0004_create_courses_tables.php
│   │   │   ├── 0005_create_announcements_tables.php
│   │   │   ├── 0006_create_attendance_tables.php
│   │   │   ├── 0007_create_assignments_tables.php
│   │   │   ├── 0008_create_schedules_tables.php
│   │   │   └── 0009_create_notifications_logs_settings_tables.php
│   │   └── seeders/
│   │       └── DatabaseSeeder.php    # بيانات تجريبية كاملة
│   ├── lang/                         # ملفات اللغة
│   │   ├── ar/
│   │   │   ├── auth.php
│   │   │   └── messages.php
│   │   └── en/
│   ├── public/
│   │   └── index.php                 # Entry point
│   ├── resources/
│   │   └── views/
│   │       ├── reports/
│   │       │   └── attendance.blade.php  # قالب PDF
│   │       └── welcome.blade.php
│   ├── routes/
│   │   ├── api.php                   # كل مسارات API
│   │   ├── console.php               # Scheduled tasks
│   │   └── web.php
│   ├── storage/                      # ملفات مرفوعة + logs
│   ├── .env.example
│   ├── artisan
│   └── composer.json
│
├── frontend/                         # React 19 + Vite + TS
│   ├── public/
│   │   └── favicon.svg
│   ├── src/
│   │   ├── components/
│   │   │   ├── ui/                   # Shadcn UI primitives
│   │   │   │   ├── avatar.tsx
│   │   │   │   ├── badge.tsx
│   │   │   │   ├── button.tsx
│   │   │   │   ├── card.tsx
│   │   │   │   ├── checkbox.tsx
│   │   │   │   ├── dialog.tsx
│   │   │   │   ├── dropdown-menu.tsx
│   │   │   │   ├── input.tsx
│   │   │   │   ├── label.tsx
│   │   │   │   ├── progress.tsx
│   │   │   │   ├── select.tsx
│   │   │   │   ├── skeleton.tsx
│   │   │   │   ├── switch.tsx
│   │   │   │   ├── tabs.tsx
│   │   │   │   └── textarea.tsx
│   │   │   └── layout/
│   │   │       └── DashboardLayout.tsx
│   │   ├── lib/
│   │   │   ├── api.ts                # Axios instance
│   │   │   ├── api-services.ts       # كل استدعاءات API
│   │   │   └── utils.ts              # Helper functions
│   │   ├── i18n/
│   │   │   └── config.ts             # i18next setup + ar/en
│   │   ├── pages/
│   │   │   ├── auth/
│   │   │   │   ├── ForgotPasswordPage.tsx
│   │   │   │   ├── LoginPage.tsx
│   │   │   │   └── RegisterPage.tsx
│   │   │   ├── announcements/
│   │   │   │   └── AnnouncementsPage.tsx
│   │   │   ├── assignments/
│   │   │   │   └── AssignmentsPage.tsx
│   │   │   ├── attendance/
│   │   │   │   └── AttendancePage.tsx
│   │   │   ├── batches/
│   │   │   │   └── BatchesPage.tsx
│   │   │   ├── courses/
│   │   │   │   └── CoursesPage.tsx
│   │   │   ├── dashboard/
│   │   │   │   └── DashboardPage.tsx
│   │   │   ├── notifications/
│   │   │   │   └── NotificationsPage.tsx
│   │   │   ├── profile/
│   │   │   │   └── ProfilePage.tsx
│   │   │   ├── reports/
│   │   │   │   └── ReportsPage.tsx
│   │   │   ├── schedule/
│   │   │   │   └── SchedulePage.tsx
│   │   │   ├── structure/
│   │   │   │   └── StructurePage.tsx
│   │   │   ├── students/
│   │   │   │   └── StudentsPage.tsx
│   │   │   └── telegram/
│   │   │       └── TelegramPage.tsx
│   │   ├── store/                    # Zustand
│   │   │   ├── auth.ts
│   │   │   └── ui.ts
│   │   ├── types/
│   │   │   └── index.ts              # كل TypeScript interfaces
│   │   ├── App.tsx                   # Router + Routes
│   │   ├── main.tsx                  # Entry point
│   │   └── index.css                 # Tailwind + globals
│   ├── .env.example
│   ├── index.html
│   ├── package.json
│   ├── postcss.config.js
│   ├── tailwind.config.js
│   ├── tsconfig.json
│   ├── tsconfig.node.json
│   └── vite.config.ts
│
├── database/
│   └── ubms_schema.sql               # SQL dump كامل
│
├── docs/                             # التوثيق
│   ├── ADMIN_MANUAL.md
│   ├── API_DOCUMENTATION.md
│   ├── CONFIGURATION.md
│   ├── DATABASE.md
│   ├── INSTALLATION.md
│   ├── PROJECT_STRUCTURE.md          # هذا الملف
│   ├── SHARED_HOSTING.md
│   ├── TELEGRAM_SETUP.md
│   ├── USER_MANUAL_REP.md
│   ├── USER_MANUAL_STUDENT.md
│   └── ubms-postman.json
│
├── README.md
└── .gitignore
```

## التقنيات المستخدمة

### Backend
| التقنية | الإصدار | الاستخدام |
|--------|--------|----------|
| PHP | 8.3 | لغة الخادم |
| Laravel | 12 | الإطار |
| MySQL | 8.0+ | قاعدة البيانات |
| Sanctum | 4 | المصادقة |
| Spatie Permission | 6 | الأدوار والصلاحيات |
| Simple QR Code | 2 | توليد QR |
| Maatwebsite Excel | 3 | تصدير Excel |
| DomPDF | 3 | تصدير PDF |
| Telegram Bot SDK | 3 | تكامل تيليجرام |

### Frontend
| التقنية | الإصدار | الاستخدام |
|--------|--------|----------|
| React | 19 | مكتبة UI |
| Vite | 6 | أداة البناء |
| TypeScript | 5.7 | الأنواع |
| TailwindCSS | 3.4 | التصميم |
| Shadcn UI | - | مكونات UI |
| React Router | 7 | التوجيه |
| React Query | 5 | إدارة الحالة الخادمة |
| Zustand | 5 | إدارة الحالة المحلية |
| Framer Motion | 11 | الرسوم المتحركة |
| Recharts | 2 | الرسوم البيانية |
| i18next | 24 | الترجمة |
| React Hook Form | 7 | النماذج |
| Zod | 3 | التحقق |

## مبادئ التصميم

1. **Service Layer**: منطق العمل في `app/Services/` لا في Controllers
2. **Resource Patterns**: العلاقات معرفة بوضوح في النماذج
3. **Policy-based Authorization**: كل عملية محمية بـ Policy
4. **Form Request Validation**: التحقق منفصل في `app/Http/Requests/`
5. **Type-safe Frontend**: كل البيانات لها TypeScript types
6. **Centralized API Client**: كل استدعاءات API في `lib/api-services.ts`
7. **RTL First**: التصميم يدعم العربية أصلياً
8. **Dark Mode by Default**: الوضع الداكن هو الافتراضي

</div>
