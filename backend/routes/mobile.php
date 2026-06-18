<?php

use App\Http\Controllers\MobileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Routes (NativePHP App)
|--------------------------------------------------------------------------
| These routes serve the mobile app built with NativePHP.
| They use session-based auth (not tokens) and return Blade views.
*/

// Auth (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/mobile/login', [MobileController::class, 'showLogin'])->name('mobile.login');
    Route::post('/mobile/login', [MobileController::class, 'login']);
    Route::get('/mobile/register', [MobileController::class, 'showRegister']);
    Route::post('/mobile/register', [MobileController::class, 'register']);
    Route::get('/mobile/forgot-password', [MobileController::class, 'showForgotPassword']);
});

// Authenticated mobile routes
Route::middleware(['auth'])->prefix('mobile')->group(function () {
    Route::post('/logout', [MobileController::class, 'logout'])->name('mobile.logout');

    // Dashboard
    Route::get('/dashboard', [MobileController::class, 'dashboard'])->name('mobile.dashboard');

    // Announcements
    Route::get('/announcements', [MobileController::class, 'announcementsIndex']);
    Route::get('/announcements/create', [MobileController::class, 'announcementCreate'])->middleware('can:create,App\Models\Announcement');
    Route::post('/announcements', [MobileController::class, 'announcementStore'])->middleware('can:create,App\Models\Announcement');
    Route::get('/announcements/{announcement}', [MobileController::class, 'announcementShow']);
    Route::post('/announcements/{announcement}/mark-read', [MobileController::class, 'announcementMarkRead']);
    Route::get('/announcements/{announcement}/attachments/{attachmentId}/download', [MobileController::class, 'announcementDownloadAttachment']);

    // Attendance
    Route::get('/attendance', [MobileController::class, 'attendanceIndex']);
    Route::get('/attendance/scan', [MobileController::class, 'attendanceScan']);
    Route::post('/attendance/scan', [MobileController::class, 'attendanceScanSubmit']);
    Route::get('/attendance/lectures/create', [MobileController::class, 'lectureCreate']);
    Route::post('/attendance/lectures', [MobileController::class, 'lectureStore']);
    Route::get('/attendance/lectures/{lecture}', [MobileController::class, 'lectureShow']);
    Route::post('/attendance/lectures/{lecture}/refresh-qr', [MobileController::class, 'lectureRefreshQr']);
    Route::post('/attendance/lectures/{lecture}/lock', [MobileController::class, 'lectureLock']);

    // Assignments
    Route::get('/assignments', [MobileController::class, 'assignmentsIndex']);
    Route::get('/assignments/{assignment}', [MobileController::class, 'assignmentShow']);
    Route::get('/assignments/{assignment}/attachments/{attachmentId}/download', [MobileController::class, 'assignmentDownloadAttachment']);

    // Schedule
    Route::get('/schedule', [MobileController::class, 'scheduleIndex']);

    // Notifications
    Route::get('/notifications', [MobileController::class, 'notificationsIndex']);
    Route::post('/notifications/{notification}/read', [MobileController::class, 'notificationMarkRead']);
    Route::post('/notifications/read-all', [MobileController::class, 'notificationsMarkAllRead']);

    // Profile
    Route::get('/profile', [MobileController::class, 'profileIndex']);
    Route::get('/profile/edit', [MobileController::class, 'profileEdit']);
    Route::put('/profile', [MobileController::class, 'profileUpdate']);
    Route::get('/profile/password', [MobileController::class, 'profilePassword']);
    Route::put('/profile/password', [MobileController::class, 'profilePasswordUpdate']);

    // Telegram
    Route::get('/telegram', [MobileController::class, 'telegramIndex']);
    Route::post('/telegram/generate-code', [MobileController::class, 'telegramGenerateCode']);
    Route::post('/telegram/disconnect', [MobileController::class, 'telegramDisconnect']);
});

// Mobile root redirect
Route::get('/mobile', function () {
    return auth()->check()
        ? redirect('/mobile/dashboard')
        : redirect('/mobile/login');
});
