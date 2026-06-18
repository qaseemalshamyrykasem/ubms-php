<?php

use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\StructureController;
use App\Http\Controllers\Api\TelegramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    // Telegram webhook (public)
    Route::post('telegram/webhook', [TelegramController::class, 'webhook']);

    Route::middleware('auth:sanctum')->group(function () {

        // Auth (authenticated)
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('search', [DashboardController::class, 'globalSearch']);

        // University structure
        Route::get('structure/hierarchy', [StructureController::class, 'hierarchy']);
        Route::get('universities', [StructureController::class, 'universities']);
        Route::get('colleges', [StructureController::class, 'colleges']);
        Route::get('departments', [StructureController::class, 'departments']);
        Route::get('levels', [StructureController::class, 'levels']);
        Route::get('sections', [StructureController::class, 'sections']);
        Route::get('batches', [StructureController::class, 'batches']);
        Route::post('batches', [StructureController::class, 'createBatch']);
        Route::get('batches/{batch}', [StructureController::class, 'showBatch']);
        Route::get('batches/{batch}/students', [StructureController::class, 'batchStudents']);

        // Announcements
        Route::apiResource('announcements', AnnouncementController::class);
        Route::post('announcements/{announcement}/pin', [AnnouncementController::class, 'togglePin']);
        Route::get('announcements/{announcement}/stats', [AnnouncementController::class, 'stats']);

        // Attendance
        Route::get('attendance/lectures', [AttendanceController::class, 'lectures']);
        Route::post('attendance/lectures', [AttendanceController::class, 'createLecture']);
        Route::post('attendance/lectures/{lecture}/refresh-qr', [AttendanceController::class, 'refreshQr']);
        Route::get('attendance/lectures/{lecture}/qr', [AttendanceController::class, 'qrCode']);
        Route::post('attendance/lectures/{lecture}/submit', [AttendanceController::class, 'submit']);
        Route::post('attendance/lectures/{lecture}/lock', [AttendanceController::class, 'lock']);
        Route::post('attendance/scan', [AttendanceController::class, 'scan']);
        Route::get('attendance/my-stats', [AttendanceController::class, 'myStats']);
        Route::get('attendance/my-history', [AttendanceController::class, 'myHistory']);
        Route::get('attendance/batch-stats', [AttendanceController::class, 'batchStats']);

        // Assignments
        Route::apiResource('assignments', AssignmentController::class);
        Route::get('assignments/{assignment}/attachments/{attachmentId}/download', [AssignmentController::class, 'downloadAttachment']);

        // Courses
        Route::apiResource('courses', CourseController::class);
        Route::post('courses/{course}/files', [CourseController::class, 'uploadFile']);
        Route::get('courses/{course}/files/{file}/download', [CourseController::class, 'downloadFile']);

        // Schedules
        Route::apiResource('schedules', ScheduleController::class);

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
        Route::delete('notifications', [NotificationController::class, 'clearAll']);

        // Telegram
        Route::get('telegram/status', [TelegramController::class, 'status']);
        Route::post('telegram/generate-code', [TelegramController::class, 'generateCode']);
        Route::post('telegram/disconnect', [TelegramController::class, 'disconnect']);
        Route::post('telegram/test', [TelegramController::class, 'testMessage']);

        // Reports
        Route::get('reports/attendance/{batchId}/{format}', [ReportController::class, 'attendance']);
        Route::get('reports/students/{batchId}', [ReportController::class, 'students']);
        Route::get('reports/announcements/{batchId}', [ReportController::class, 'announcements']);
        Route::get('reports/assignments/{batchId}', [ReportController::class, 'assignments']);
        Route::get('reports/statistics/{batchId}/{format?}', [ReportController::class, 'statistics']);
        Route::get('reports/stats/{batchId}', [ReportController::class, 'stats']);
    });
});
