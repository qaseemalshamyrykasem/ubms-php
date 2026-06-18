<?php

return [
    'required' => 'هذا الحقل مطلوب.',
    'batch' => [
        'required' => 'يجب تحديد دفعة.',
        'created' => 'تم إنشاء الدفعة بنجاح.',
    ],
    'announcements' => [
        'created' => 'تم نشر الإعلان بنجاح.',
        'updated' => 'تم تحديث الإعلان.',
        'deleted' => 'تم حذف الإعلان.',
        'types' => [
            'holiday' => 'عطلة',
            'assignment' => 'واجب',
            'lecture' => 'محاضرة',
            'schedule' => 'جدول',
            'general' => 'عام',
            'urgent' => 'عاجل',
            'emergency' => 'طوارئ',
            'meeting' => 'اجتماع',
            'important' => 'مهم',
        ],
    ],
    'attendance' => [
        'lecture_created' => 'تم إنشاء المحاضرة وجاهزة لاستقبال الحضور.',
        'submitted' => 'تم تسجيل الحضور.',
        'locked' => 'تم قفل تسجيل الحضور لهذه المحاضرة.',
        'recorded' => 'تم تسجيل حضورك بنجاح.',
        'qr_invalid' => 'رمز QR غير صالح.',
        'qr_expired' => 'انتهت صلاحية رمز QR. اطلب من الممثل إعادة توليده.',
        'duplicate' => 'تم تسجيل حضورك مسبقاً.',
        'status_present' => 'حاضر',
        'status_absent' => 'غائب',
        'status_late' => 'متأخر',
        'status_excused' => 'بعذر',
    ],
    'assignments' => [
        'created' => 'تم إنشاء الواجب.',
        'updated' => 'تم تحديث الواجب.',
        'deleted' => 'تم حذف الواجب.',
    ],
    'courses' => [
        'created' => 'تم إنشاء المقرر.',
        'updated' => 'تم تحديث المقرر.',
        'deleted' => 'تم حذف المقرر.',
        'file_uploaded' => 'تم رفع الملف.',
    ],
    'schedules' => [
        'created' => 'تمت إضافة المحاضرة للجدول.',
        'updated' => 'تم تحديث الجدول.',
        'deleted' => 'تم حذف المحاضرة من الجدول.',
    ],
    'notifications' => [
        'marked_read' => 'تم وضع علامة مقروء.',
        'all_marked_read' => 'تم تحديد جميع الإشعارات كمقروءة.',
        'deleted' => 'تم حذف الإشعار.',
        'cleared' => 'تم مسح جميع الإشعارات.',
        'assignment_posted' => 'واجب جديد',
    ],
    'telegram' => [
        'verified' => '✅ تم ربط حسابك بتيليجرام بنجاح! ستصلك الإشعارات الآن.',
        'disconnected' => 'تم فصل حساب تيليجرام.',
        'not_connected' => 'لم يتم ربط حساب تيليجرام بعد.',
        'test_message' => 'هذه رسالة تجريبية من نظام إدارة الدفعات الجامعية.',
    ],
    'file' => [
        'not_found' => 'الملف غير موجود.',
    ],
    'page' => 'صفحة',
];
