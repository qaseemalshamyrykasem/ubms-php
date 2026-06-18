<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\College;
use App\Models\CollegeAdmin;
use App\Models\Course;
use App\Models\Department;
use App\Models\Lecture;
use App\Models\Level;
use App\Models\Schedule;
use App\Models\Section;
use App\Models\Setting;
use App\Models\SiteNotification;
use App\Models\Student;
use App\Models\SuperAdmin;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedSettings();
        $this->seedUniversity();
        $this->seedDemoAnnouncements();
        $this->seedDemoAttendance();
        $this->seedDemoAssignments();
        $this->seedDemoSchedules();
    }

    private function seedRolesAndPermissions(): void
    {
        $roles = ['super_admin', 'college_admin', 'representative', 'student'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        $permissions = [
            'view announcements', 'create announcements', 'edit announcements', 'delete announcements',
            'view attendance', 'create attendance', 'edit attendance',
            'view assignments', 'create assignments', 'edit assignments', 'delete assignments',
            'view courses', 'create courses', 'edit courses', 'delete courses',
            'view schedules', 'create schedules', 'edit schedules', 'delete schedules',
            'view batches', 'create batches', 'edit batches',
            'view students', 'manage students',
            'view reports', 'export reports',
            'manage users', 'manage system',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $superAdmin = Role::where('name', 'super_admin')->where('guard_name', 'web')->first();
        $superAdmin->givePermissionTo(Permission::where('guard_name', 'web')->get());

        $collegeAdmin = Role::where('name', 'college_admin')->where('guard_name', 'web')->first();
        $collegeAdmin->givePermissionTo([
            'view batches', 'create batches', 'edit batches',
            'view students', 'manage students',
            'view courses', 'create courses', 'edit courses',
            'view reports', 'export reports',
            'view announcements', 'view attendance', 'view assignments', 'view schedules',
        ]);

        $rep = Role::where('name', 'representative')->where('guard_name', 'web')->first();
        $rep->givePermissionTo([
            'view announcements', 'create announcements', 'edit announcements', 'delete announcements',
            'view attendance', 'create attendance', 'edit attendance',
            'view assignments', 'create assignments', 'edit assignments', 'delete assignments',
            'view courses', 'view schedules', 'create schedules', 'edit schedules', 'delete schedules',
            'view students', 'view reports', 'export reports',
        ]);

        $student = Role::where('name', 'student')->where('guard_name', 'web')->first();
        $student->givePermissionTo([
            'view announcements', 'view attendance', 'view assignments', 'view courses', 'view schedules',
        ]);
    }

    private function seedSettings(): void
    {
        $defaults = [
            'university.name' => 'University of Aden',
            'university.name_ar' => 'جامعة عدن',
            'app.locale' => 'ar',
            'app.timezone' => 'Asia/Aden',
            'app.dark_mode_default' => 'true',
            'uploads.max_size_mb' => '20',
            'uploads.allowed_types' => 'jpg,jpeg,png,pdf,docx,zip,rar,xlsx,pptx',
            'telegram.enabled' => 'true',
            'attendance.qr_ttl_minutes' => '15',
            'attendance.late_threshold_minutes' => '15',
        ];
        foreach ($defaults as $k => $v) {
            Setting::set($k, $v, 'general');
        }
    }

    private function seedUniversity(): void
    {
        $uni = University::create([
            'name' => 'University of Aden',
            'name_ar' => 'جامعة عدن',
            'code' => 'UOA',
            'country' => 'Yemen',
            'city' => 'Aden',
            'address' => 'Aden, Yemen',
            'phone' => '+967 2 123 4567',
            'email' => 'info@uoa.edu.ye',
            'website' => 'https://www.uoa.edu.ye',
            'is_active' => true,
        ]);

        $college = College::create([
            'university_id' => $uni->id,
            'name' => 'College of Engineering',
            'name_ar' => 'كلية الهندسة',
            'code' => 'ENG',
            'dean_name' => 'د. أحمد المقطري',
            'is_active' => true,
        ]);

        $dept = Department::create([
            'college_id' => $college->id,
            'name' => 'Computer Science',
            'name_ar' => 'علوم الحاسوب',
            'code' => 'CS',
            'head_name' => 'د. سالم العولقي',
            'description' => 'قسم علوم الحاسوب - برمجة، شبكات، ذكاء اصطناعي',
            'is_active' => true,
        ]);

        $level = Level::create([
            'department_id' => $dept->id,
            'name' => 'Level 3',
            'name_ar' => 'المستوى الثالث',
            'level_number' => 3,
            'is_active' => true,
        ]);

        $section = Section::create([
            'level_id' => $level->id,
            'name' => 'Section A',
            'name_ar' => 'شعبة (أ)',
            'code' => 'A',
            'capacity' => 60,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'section_id' => $section->id,
            'name' => '2024-2025',
            'name_ar' => 'دفعة 2024-2025',
            'code' => 'CS-2024-A',
            'start_year' => 2024,
            'end_year' => 2025,
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        // ---- Demo users ----
        $superAdminUser = User::create([
            'name' => 'Super Admin',
            'name_ar' => 'مدير النظام',
            'email' => 'admin@ubms.local',
            'password' => Hash::make('password'),
            'status' => 'active',
            'locale' => 'ar',
            'email_verified_at' => now(),
        ]);
        $superAdminUser->assignRole('super_admin');
        SuperAdmin::create(['user_id' => $superAdminUser->id, 'employee_id' => 'EMP-001']);

        $collegeAdminUser = User::create([
            'name' => 'College Admin',
            'name_ar' => 'مدير الكلية',
            'email' => 'college@ubms.local',
            'password' => Hash::make('password'),
            'status' => 'active',
            'locale' => 'ar',
            'email_verified_at' => now(),
        ]);
        $collegeAdminUser->assignRole('college_admin');
        CollegeAdmin::create(['user_id' => $collegeAdminUser->id, 'college_id' => $college->id, 'employee_id' => 'EMP-100']);

        $repUser = User::create([
            'name' => 'Rep Mohammed',
            'name_ar' => 'محمد علي (ممثل الدفعة)',
            'email' => 'rep@ubms.local',
            'password' => Hash::make('password'),
            'status' => 'active',
            'locale' => 'ar',
            'phone' => '+967 777 123 456',
            'email_verified_at' => now(),
        ]);
        $repUser->assignRole('representative');
        \App\Models\Representative::create([
            'user_id' => $repUser->id,
            'batch_id' => $batch->id,
            'student_id' => 'CS-2024-A-001',
            'appointed_at' => now(),
        ]);

        // Demo students (30)
        $firstNames = ['أحمد','محمد','علي','سالم','عبدالله','حسن','يوسف','إبراهيم','خالد','ماجد','نورا','فاطمة','سارة','هند','ريم','آلاء','منى','لمى','دانا','جنى','طارق','وليد','زياد','عمرو','حاتم','أنس','بسام','ثائر','جمال','خيرية'];
        $lastNames = ['العولقي','المقطري','الشامي','الحضرمي','الجندي','الحسيني','الصبري','الزبيدي','السعدي','الفلاحي'];
        for ($i = 0; $i < 30; $i++) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];
            $nameAr = $first . ' ' . $last;
            $enName = transliterator_transliterate('Any-Latin; Latin-ASCII; Title()', $nameAr);
            $studentUser = User::create([
                'name' => $enName ?: 'Student ' . ($i + 1),
                'name_ar' => $nameAr,
                'email' => 'student' . ($i + 1) . '@ubms.local',
                'password' => Hash::make('password'),
                'status' => 'active',
                'locale' => 'ar',
                'phone' => '+967 7' . rand(10000000, 99999999),
                'email_verified_at' => now(),
            ]);
            $studentUser->assignRole('student');
            Student::create([
                'user_id' => $studentUser->id,
                'batch_id' => $batch->id,
                'student_id' => 'CS-2024-A-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'enrolled_at' => '2024-09-01',
            ]);
        }

        // ---- Courses ----
        $courses = [
            ['name' => 'Database Systems', 'name_ar' => 'أنظمة قواعد البيانات', 'code' => 'CS301', 'credit_hours' => 3, 'instructor_name' => 'د. سالم العولقي'],
            ['name' => 'Web Development', 'name_ar' => 'تطوير الويب', 'code' => 'CS302', 'credit_hours' => 4, 'instructor_name' => 'أ. محمد باشا'],
            ['name' => 'Operating Systems', 'name_ar' => 'نظم التشغيل', 'code' => 'CS303', 'credit_hours' => 3, 'instructor_name' => 'د. عبده الأغبري'],
            ['name' => 'Software Engineering', 'name_ar' => 'هندسة البرمجيات', 'code' => 'CS304', 'credit_hours' => 3, 'instructor_name' => 'د. أنور الشميري'],
            ['name' => 'Computer Networks', 'name_ar' => 'شبكات الحاسوب', 'code' => 'CS305', 'credit_hours' => 3, 'instructor_name' => 'د. مراد باوزير'],
        ];
        $courseIds = [];
        foreach ($courses as $c) {
            $c['department_id'] = $dept->id;
            $c['is_active'] = true;
            $course = Course::create($c);
            $courseIds[] = $course->id;
            $batch->courses()->attach($course->id, ['level_id' => $level->id]);
        }
    }

    private function seedDemoAnnouncements(): void
    {
        $batch = Batch::where('code', 'CS-2024-A')->first();
        $rep = User::where('email', 'rep@ubms.local')->first();
        $courseIds = $batch->courses->pluck('id')->toArray();

        $samples = [
            ['title' => 'تنبيه: محاضرة قواعد البيانات غداً', 'type' => 'lecture', 'body' => 'نُذكّر جميع الطلاب بأن محاضرة قواعد البيانات ستُقام غداً الساعة 9 صباحاً في القاعة 305. يرجى الحضور مبكراً.'],
            ['title' => 'إعلان عطلة يوم الأربعاء', 'type' => 'holiday', 'body' => 'بمناسبة العطلة الأسبوعية، تُعطّل الدراسة يوم الأربعاء. تستأنف المحاضرات يوم الخميس.'],
            ['title' => 'واجب: تصميم قاعدة بيانات لمكتبة', 'type' => 'assignment', 'body' => 'طلب منا د. سالم تصميم قاعدة بيانات كاملة لمكتبة جامعية مع المخطط ER والتطبيع حتى 3NF.'],
            ['title' => 'اجتماع مهم يوم الأحد', 'type' => 'meeting', 'body' => 'سيعقد اجتماع لطلاب الدفعة يوم الأحد القادم لمناقشة جدول الاختبارات النهائية.'],
            ['title' => 'إعلان عاجل: تأجيل محاضرة اليوم', 'type' => 'urgent', 'body' => 'بسبب ظرف طارئ، تم تأجيل محاضرة شبكات الحاسوب لليوم. سيتم تحديد موعد بديل لاحقاً.'],
            ['title' => 'تعديل جدول المحاضرات', 'type' => 'schedule', 'body' => 'تم تعديل جدول محاضرات هذا الأسبوع. يرجى مراجعة الجدول المحدّث في صفحة الجدول.'],
            ['title' => 'مهم: استلام الواجب الثاني', 'type' => 'important', 'body' => 'تذكير بأن موعد تسليم الواجب الثاني لتطوير الويب هو نهاية الأسبوع. لا تُقبل الأعمال المتأخرة.'],
            ['title' => 'تنبيه طوارئ: إخلاء القاعة', 'type' => 'emergency', 'body' => 'في حال سماع جرس الإنذار، يرجى إخلاء القاعات بهدوء واتباع تعليمات السلامة.'],
            ['title' => 'محاضرة استثنائية يوم الخميس', 'type' => 'general', 'body' => 'ستُقام محاضرة استثنائية في تطوير الويب يوم الخميس الساعة 11 صباحاً لتعويض المحاضرة الفائتة.'],
        ];

        foreach ($samples as $i => $s) {
            Announcement::create([
                'batch_id' => $batch->id,
                'user_id' => $rep->id,
                'course_id' => $courseIds[$i % count($courseIds)],
                'title' => $s['title'],
                'body' => $s['body'],
                'type' => $s['type'],
                'is_pinned' => $i < 2,
                'is_published' => true,
                'published_at' => now()->subDays(rand(0, 15)),
                'send_telegram' => false,
                'telegram_sent' => false,
            ]);
        }
    }

    private function seedDemoAttendance(): void
    {
        $batch = Batch::where('code', 'CS-2024-A')->first();
        $rep = User::where('email', 'rep@ubms.local')->first();
        $students = $batch->students;
        $courses = $batch->courses;

        // Create lectures for last 2 weeks
        for ($d = 14; $d >= 0; $d--) {
            $date = now()->subDays($d);
            if (in_array($date->dayOfWeek, [5, 6])) continue; // Fri/Sat weekend

            foreach ($courses as $course) {
                $lecture = Lecture::create([
                    'batch_id' => $batch->id,
                    'course_id' => $course->id,
                    'title' => $course->name_ar . ' - محاضرة',
                    'date' => $date->format('Y-m-d'),
                    'start_time' => '09:00',
                    'end_time' => '10:30',
                    'room' => '305',
                    'qr_token' => \Str::uuid()->toString(),
                    'qr_expires_at' => $date->copy()->addMinutes(15),
                    'attendance_locked' => true,
                    'created_by' => $rep->id,
                ]);

                foreach ($students as $student) {
                    $roll = rand(1, 100);
                    $status = $roll <= 75 ? 'present' : ($roll <= 85 ? 'late' : ($roll <= 95 ? 'absent' : 'excused'));

                    Attendance::create([
                        'lecture_id' => $lecture->id,
                        'student_id' => $student->user_id,
                        'batch_student_id' => $student->id,
                        'status' => $status,
                        'verification_method' => rand(0, 1) ? 'qr' : 'manual',
                        'recorded_at' => $date->copy()->addMinutes(rand(0, 20)),
                        'recorded_by' => $rep->id,
                    ]);
                }
            }
        }
    }

    private function seedDemoAssignments(): void
    {
        $batch = Batch::where('code', 'CS-2024-A')->first();
        $rep = User::where('email', 'rep@ubms.local')->first();
        $courses = $batch->courses;

        $assignments = [
            ['title' => 'واجب 1: تصميم ER Diagram', 'description' => 'تصميم مخطط ER كامل لنظام إدارة مكتبة مع جميع الكيانات والعلاقات والصفات. التسليم بصيغة PDF.'],
            ['title' => 'واجب 2: تطبيق React لإدارة المهام', 'description' => 'بناء تطبيق Todo List باستخدام React مع إمكانية الإضافة والحذف والتعديل.'],
            ['title' => 'تقرير: أنظمة التشغيل', 'description' => 'بحث مقارن بين ثلاثة أنظمة تشغيل (Windows, Linux, macOS) من حيث المميزات والعيوب.'],
            ['title' => 'مشروع: تصميم نظام إدارة دفعات', 'description' => 'تصميم وبناء نظام متكامل لإدارة الدفعات الجامعية مع جميع المتطلبات الوظيفية وغير الوظيفية.'],
        ];

        foreach ($assignments as $i => $a) {
            Assignment::create([
                'batch_id' => $batch->id,
                'course_id' => $courses[$i % count($courses)]->id,
                'user_id' => $rep->id,
                'title' => $a['title'],
                'description' => $a['description'],
                'deadline' => now()->addDays(rand(5, 30)),
                'max_grade' => 100,
                'allow_late_submission' => true,
                'late_penalty_percent' => 10,
                'notify_telegram' => false,
                'telegram_notified' => false,
            ]);
        }
    }

    private function seedDemoSchedules(): void
    {
        $batch = Batch::where('code', 'CS-2024-A')->first();
        $courses = $batch->courses;

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        $timeSlots = [
            ['08:00', '09:30'],
            ['09:45', '11:15'],
            ['11:30', '13:00'],
            ['13:30', '15:00'],
        ];
        $rooms = ['305', '312', 'Lab1', 'Lab2'];

        foreach ($days as $idx => $day) {
            foreach ($courses as $cIdx => $course) {
                $slot = $timeSlots[$cIdx % count($timeSlots)];
                Schedule::create([
                    'batch_id' => $batch->id,
                    'course_id' => $course->id,
                    'day' => $day,
                    'start_time' => $slot[0],
                    'end_time' => $slot[1],
                    'room' => $rooms[$cIdx % count($rooms)],
                    'building' => 'مبنى الكلية',
                    'instructor_name' => $course->instructor_name,
                    'is_recurring' => true,
                    'effective_from' => '2024-09-01',
                    'effective_until' => '2025-06-30',
                ]);
            }
        }
    }
}
