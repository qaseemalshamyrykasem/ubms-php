@extends('mobile.layouts.app')
@section('title', 'لوحة التحكم - UBMS')

@php
    $user = auth()->user();
    $role = $user->primaryRole();
    $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">
        <span>🎓</span>
        <span>UBMS</span>
    </div>
    <div class="app-bar-actions">
        <a href="/mobile/notifications" class="icon-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"/></svg>
            @php $unread = \App\Models\SiteNotification::where('user_id', $user->id)->where('is_read', false)->count(); @endphp
            @if ($unread > 0)
                <span class="badge-dot">{{ $unread > 99 ? '99+' : $unread }}</span>
            @endif
        </a>
    </div>
</div>

<div class="main fade-in">
    {{-- Welcome card --}}
    <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--accent)); border: none;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700;">
                {{ mb_substr($user->name_ar ?? $user->name, 0, 1) }}
            </div>
            <div style="flex: 1;">
                <div style="font-size: 18px; font-weight: 700;">مرحباً، {{ $user->name_ar ?? $user->name }}</div>
                <div style="font-size: 12px; opacity: 0.8;">
                    @if ($batch)
                        {{ $batch->code }} - {{ $batch->name_ar }}
                    @endif
                </div>
            </div>
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white;">
                {{ $role === 'student' ? 'طالب' : ($role === 'representative' ? 'ممثل' : $role) }}
            </span>
        </div>
    </div>

    @if ($role === 'student')
        @php
            $attendanceStats = app(\App\Services\AttendanceService::class)->studentStats($user);
        @endphp
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd;">📢</div>
                <div class="stat-value">{{ \App\Models\Announcement::where('batch_id', $batch?->id)->published()->count() }}</div>
                <div class="stat-label">إعلانات</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.2); color: #c4b5fd;">📚</div>
                <div class="stat-value">{{ \App\Models\Assignment::where('batch_id', $batch?->id)->count() }}</div>
                <div class="stat-label">واجبات</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7;">✅</div>
                <div class="stat-value">{{ $attendanceStats['present'] ?? 0 }}</div>
                <div class="stat-label">حاضر</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5;">❌</div>
                <div class="stat-value">{{ $attendanceStats['absent'] ?? 0 }}</div>
                <div class="stat-label">غائب</div>
            </div>
        </div>

        {{-- Attendance rate --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">نسبة الحضور</div>
                <span class="badge {{ ($attendanceStats['rate'] ?? 0) >= 75 ? 'badge-success' : 'badge-warning' }}">
                    {{ $attendanceStats['rate'] ?? 0 }}%
                </span>
            </div>
            <div class="progress" style="margin-top: 8px;">
                <div class="progress-bar" style="width: {{ $attendanceStats['rate'] ?? 0 }}%;"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 12px; color: var(--text-muted);">
                <span>المجموع: {{ $attendanceStats['total_lectures'] ?? 0 }} محاضرة</span>
                <span>متأخر: {{ $attendanceStats['late'] ?? 0 }}</span>
            </div>
        </div>

    @elseif ($role === 'representative')
        @php
            $batchStats = app(\App\Services\AttendanceService::class)->batchStats($batch->id);
        @endphp
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd;">👥</div>
                <div class="stat-value">{{ \App\Models\Student::where('batch_id', $batch->id)->where('status', 'active')->count() }}</div>
                <div class="stat-label">طالب</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.2); color: #c4b5fd;">📢</div>
                <div class="stat-value">{{ \App\Models\Announcement::where('batch_id', $batch->id)->count() }}</div>
                <div class="stat-label">إعلان</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2); color: #fcd34d;">📚</div>
                <div class="stat-value">{{ \App\Models\Assignment::where('batch_id', $batch->id)->count() }}</div>
                <div class="stat-label">واجب</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7;">📅</div>
                <div class="stat-value">{{ \App\Models\Lecture::where('batch_id', $batch->id)->count() }}</div>
                <div class="stat-label">محاضرة</div>
            </div>
        </div>

        {{-- Attendance summary --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">إحصائيات الحضور</div>
                <span class="badge {{ $batchStats['rate'] >= 75 ? 'badge-success' : 'badge-warning' }}">{{ $batchStats['rate'] }}%</span>
            </div>
            <div class="progress" style="margin-top: 8px;">
                <div class="progress-bar" style="width: {{ $batchStats['rate'] }}%;"></div>
            </div>
            <div style="display: flex; justify-content: space-around; margin-top: 12px; text-align: center;">
                <div>
                    <div style="font-size: 20px; font-weight: 700; color: #6ee7b7;">{{ $batchStats['present'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">حاضر</div>
                </div>
                <div>
                    <div style="font-size: 20px; font-weight: 700; color: #fcd34d;">{{ $batchStats['late'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">متأخر</div>
                </div>
                <div>
                    <div style="font-size: 20px; font-weight: 700; color: #fca5a5;">{{ $batchStats['absent'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">غائب</div>
                </div>
                <div>
                    <div style="font-size: 20px; font-weight: 700; color: #c4b5fd;">{{ $batchStats['excused'] }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">بعذر</div>
                </div>
            </div>
        </div>
    @endif

    {{-- Recent announcements --}}
    <div class="section-title">آخر الإعلانات</div>
    @php
        $recentAnnouncements = \App\Models\Announcement::where('batch_id', $batch?->id)
            ->published()
            ->with('author', 'course')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();
    @endphp
    @foreach ($recentAnnouncements as $announcement)
        <a href="/mobile/announcements/{{ $announcement->id }}" class="list-item">
            <div class="list-item-content">
                <div class="list-item-title">{{ $announcement->title }}</div>
                <div class="list-item-subtitle">{{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</div>
            </div>
            <span class="badge badge-{{ $announcement->type }}">
                @php
                    $types = ['holiday' => 'عطلة', 'assignment' => 'واجب', 'lecture' => 'محاضرة', 'schedule' => 'جدول', 'general' => 'عام', 'urgent' => 'عاجل', 'emergency' => 'طوارئ', 'meeting' => 'اجتماع', 'important' => 'مهم'];
                @endphp
                {{ $types[$announcement->type] ?? $announcement->type }}
            </span>
        </a>
    @endforeach

    @if ($recentAnnouncements->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>لا توجد إعلانات بعد</p>
        </div>
    @endif

    {{-- Quick actions --}}
    <div class="section-title">إجراءات سريعة</div>
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px;">
        @if ($role === 'student')
            <a href="/mobile/attendance/scan" style="text-decoration: none;">
                <div style="background: var(--bg-card); border-radius: 12px; padding: 14px; text-align: center; border: 1px solid var(--border);">
                    <div style="font-size: 24px; margin-bottom: 4px;">📷</div>
                    <div style="font-size: 10px; color: var(--text-muted);">مسح QR</div>
                </div>
            </a>
        @endif
        <a href="/mobile/schedule" style="text-decoration: none;">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 14px; text-align: center; border: 1px solid var(--border);">
                <div style="font-size: 24px; margin-bottom: 4px;">📅</div>
                <div style="font-size: 10px; color: var(--text-muted);">الجدول</div>
            </div>
        </a>
        <a href="/mobile/profile" style="text-decoration: none;">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 14px; text-align: center; border: 1px solid var(--border);">
                <div style="font-size: 24px; margin-bottom: 4px;">👤</div>
                <div style="font-size: 10px; color: var(--text-muted);">حسابي</div>
            </div>
        </a>
        <a href="/mobile/telegram" style="text-decoration: none;">
            <div style="background: var(--bg-card); border-radius: 12px; padding: 14px; text-align: center; border: 1px solid var(--border);">
                <div style="font-size: 24px; margin-bottom: 4px;">✈️</div>
                <div style="font-size: 10px; color: var(--text-muted);">تيليجرام</div>
            </div>
        </a>
    </div>
</div>

@include('mobile.partials.bottom-nav')
@endsection
