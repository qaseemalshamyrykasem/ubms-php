<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\SiteNotification;
use App\Services\AnnouncementService;
use App\Services\AttendanceService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private AnnouncementService $announcementService,
    ) {
    }

    // ==================== AUTH ====================

    public function showLogin()
    {
        return view('mobile.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $user = \App\Models\User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['حسابك غير نشط'],
            ]);
        }

        auth()->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/mobile/dashboard');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/mobile/login');
    }

    public function showRegister()
    {
        $batches = \App\Models\Batch::where('is_active', true)->get();
        return view('mobile.auth.register', compact('batches'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:30',
            'batch_id' => 'nullable|exists:batches,id',
            'student_id' => 'nullable|string|max:50',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = \App\Models\User::create($data);
        $user->assignRole('student');

        if (!empty($data['batch_id'])) {
            $batch = \App\Models\Batch::find($data['batch_id']);
            \App\Models\Student::create([
                'user_id' => $user->id,
                'batch_id' => $batch->id,
                'student_id' => $data['student_id'] ?? ($batch->code . '-' . str_pad((string) $batch->students()->count() + 1, 3, '0', STR_PAD_LEFT)),
                'enrolled_at' => now(),
            ]);
        }

        auth()->login($user);
        return redirect('/mobile/dashboard');
    }

    public function showForgotPassword()
    {
        return view('mobile.auth.forgot-password');
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        return view('mobile.dashboard');
    }

    // ==================== ANNOUNCEMENTS ====================

    public function announcementsIndex(Request $request)
    {
        $user = $request->user();
        $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;

        if (!$batch) {
            return view('mobile.announcements.index', ['announcements' => collect()->paginate(15)]);
        }

        $query = Announcement::where('batch_id', $batch->id)->published()
            ->with('author', 'course', 'attachments');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $announcements = $query->orderByDesc('is_pinned')->orderByDesc('published_at')->paginate(15);
        return view('mobile.announcements.index', compact('announcements'));
    }

    public function announcementShow(Announcement $announcement)
    {
        $this->authorize('view', $announcement);
        return view('mobile.announcements.show', compact('announcement'));
    }

    public function announcementMarkRead(Announcement $announcement, Request $request)
    {
        $this->authorize('view', $announcement);
        $this->announcementService->markRead($announcement, $request->user());
        return back()->with('success', 'تم وضع علامة مقروء');
    }

    public function announcementDownloadAttachment(Announcement $announcement, $attachmentId)
    {
        $this->authorize('view', $announcement);
        $attachment = $announcement->attachments()->findOrFail($attachmentId);
        if (!Storage::disk('public')->exists($attachment->file_path)) abort(404);
        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    // ==================== ATTENDANCE ====================

    public function attendanceIndex()
    {
        return view('mobile.attendance.index');
    }

    public function attendanceScan()
    {
        return view('mobile.attendance.scan');
    }

    public function attendanceScanSubmit(Request $request)
    {
        $data = $request->validate([
            'lecture_id' => 'required|integer',
            'token' => 'required|string',
        ]);

        try {
            $lecture = Lecture::findOrFail($data['lecture_id']);
            $attendance = $this->attendanceService->scanQr($lecture, $request->user(), $data['token']);

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل حضورك بنجاح',
                'status' => $attendance->status,
            ]);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function lectureShow(Lecture $lecture)
    {
        $this->authorize('view', $lecture);
        $lecture->load('course', 'attendances.student');
        return view('mobile.attendance.lecture', compact('lecture'));
    }

    public function lectureCreate()
    {
        $this->authorize('create', Lecture::class);
        $user = auth()->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) abort(403);
        $courses = $batch->courses;
        return view('mobile.attendance.create-lecture', compact('courses'));
    }

    public function lectureStore(Request $request)
    {
        $this->authorize('create', Lecture::class);
        $user = $request->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) abort(403);

        $data = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'nullable|string|max:255',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'room' => 'nullable|string|max:100',
        ]);

        $data['batch_id'] = $batch->id;
        $lecture = $this->attendanceService->createLecture($data, $user);
        return redirect('/mobile/attendance/lectures/' . $lecture->id)->with('success', 'تم إنشاء المحاضرة');
    }

    public function lectureRefreshQr(Lecture $lecture)
    {
        $this->authorize('update', $lecture);
        $this->attendanceService->refreshQr($lecture);
        return back()->with('success', 'تم تحديث رمز QR');
    }

    public function lectureLock(Lecture $lecture)
    {
        $this->authorize('update', $lecture);
        $this->attendanceService->lockLecture($lecture);
        return back()->with('success', 'تم قفل التسجيل');
    }

    public function announcementCreate()
    {
        $this->authorize('create', Announcement::class);
        $user = auth()->user();
        $batch = $user->representativeProfile?->batch;
        $courses = $batch?->courses ?? collect();
        return view('mobile.announcements.create', compact('courses'));
    }

    public function announcementStore(Request $request)
    {
        $this->authorize('create', Announcement::class);
        $user = $request->user();
        $batch = $user->representativeProfile?->batch;
        if (!$batch) abort(403);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
            'type' => 'required|in:holiday,assignment,lecture,schedule,general,urgent,emergency,meeting,important',
            'course_id' => 'nullable|exists:courses,id',
            'is_pinned' => 'boolean',
            'send_telegram' => 'boolean',
        ]);

        $this->announcementService->create($batch, $user, $data, $request->file('attachments', []));
        return redirect('/mobile/announcements')->with('success', 'تم نشر الإعلان');
    }

    // ==================== ASSIGNMENTS ====================

    public function assignmentsIndex()
    {
        $user = auth()->user();
        $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
        $assignments = Assignment::where('batch_id', $batch?->id)
            ->with('course', 'author', 'attachments')
            ->orderBy('deadline')
            ->paginate(15);
        return view('mobile.assignments.index', compact('assignments'));
    }

    public function assignmentShow(Assignment $assignment)
    {
        $this->authorize('view', $assignment);
        return view('mobile.assignments.show', compact('assignment'));
    }

    public function assignmentDownloadAttachment(Assignment $assignment, $attachmentId)
    {
        $this->authorize('view', $assignment);
        $attachment = $assignment->attachments()->findOrFail($attachmentId);
        if (!Storage::disk('public')->exists($attachment->file_path)) abort(404);
        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    // ==================== SCHEDULE ====================

    public function scheduleIndex()
    {
        return view('mobile.schedule.index');
    }

    // ==================== NOTIFICATIONS ====================

    public function notificationsIndex()
    {
        $notifications = SiteNotification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('mobile.notifications.index', compact('notifications'));
    }

    public function notificationMarkRead(SiteNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) abort(403);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return back();
    }

    public function notificationsMarkAllRead()
    {
        SiteNotification::where('user_id', auth()->id())->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'تم تحديد الكل كمقروء');
    }

    // ==================== PROFILE ====================

    public function profileIndex()
    {
        return view('mobile.profile.index');
    }

    public function profileEdit()
    {
        return view('mobile.profile.edit', ['user' => auth()->user()]);
    }

    public function profileUpdate(Request $request)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = $request->user();
        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);
        return redirect('/mobile/profile')->with('success', 'تم تحديث الملف');
    }

    public function profilePassword()
    {
        return view('mobile.profile.password');
    }

    public function profilePasswordUpdate(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة'],
            ]);
        }
        $user->update(['password' => Hash::make($data['password'])]);
        return redirect('/mobile/profile')->with('success', 'تم تغيير كلمة المرور');
    }

    // ==================== TELEGRAM ====================

    public function telegramIndex()
    {
        return view('mobile.telegram.index');
    }

    public function telegramGenerateCode(Request $request)
    {
        $service = app(TelegramService::class);
        $code = $service->generateVerificationCode($request->user());
        $botUsername = config('services.telegram.bot_username');
        $deepLink = $botUsername ? "https://t.me/{$botUsername}?start={$code}" : null;

        return back()->with([
            'code' => $code,
            'deep_link' => $deepLink,
        ]);
    }

    public function telegramDisconnect(Request $request)
    {
        app(TelegramService::class)->disconnect($request->user());
        return back()->with('success', 'تم فصل الحساب');
    }
}
