@extends('mobile.layouts.app')
@section('title', 'الحضور - UBMS')

@php
    $user = auth()->user();
    $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
    $role = $user->primaryRole();
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">📅 الحضور</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    @if ($role === 'student')
        {{-- Student: stats + scan button --}}
        @php
            $stats = app(\App\Services\AttendanceService::class)->studentStats($user);
        @endphp

        {{-- Big scan QR button --}}
        <a href="/mobile/attendance/scan" style="text-decoration: none; display: block; margin-bottom: 20px;">
            <div style="background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 20px; padding: 32px; text-align: center; color: white; box-shadow: 0 10px 24px rgba(99, 102, 241, 0.3);">
                <div style="font-size: 64px; margin-bottom: 12px;">📷</div>
                <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">امسح رمز QR</div>
                <div style="font-size: 12px; opacity: 0.8;">لتسجيل حضورك في المحاضرة</div>
            </div>
        </a>

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7;">✅</div>
                <div class="stat-value">{{ $stats['present'] ?? 0 }}</div>
                <div class="stat-label">حاضر</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(245, 158, 11, 0.2); color: #fcd34d;">⏰</div>
                <div class="stat-value">{{ $stats['late'] ?? 0 }}</div>
                <div class="stat-label">متأخر</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5;">❌</div>
                <div class="stat-value">{{ $stats['absent'] ?? 0 }}</div>
                <div class="stat-label">غائب</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: rgba(139, 92, 246, 0.2); color: #c4b5fd;">📝</div>
                <div class="stat-value">{{ $stats['excused'] ?? 0 }}</div>
                <div class="stat-label">بعذر</div>
            </div>
        </div>

        {{-- Overall rate --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">نسبة الحضور الإجمالية</div>
                <span class="badge {{ ($stats['rate'] ?? 0) >= 75 ? 'badge-success' : 'badge-warning' }}">{{ $stats['rate'] ?? 0 }}%</span>
            </div>
            <div class="progress" style="margin-top: 8px;">
                <div class="progress-bar" style="width: {{ $stats['rate'] ?? 0 }}%;"></div>
            </div>
            <div style="text-align: center; margin-top: 12px; font-size: 13px; color: var(--text-muted);">
                {{ $stats['present'] ?? 0 }} من {{ $stats['total_lectures'] ?? 0 }} محاضرة
            </div>
        </div>

        {{-- Recent history --}}
        <div class="section-title">آخر سجلات الحضور</div>
        @php
            $history = \App\Models\Attendance::with('lecture.course')
                ->where('student_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        @endphp
        @foreach ($history as $record)
            <div class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title">{{ $record->lecture?->course?->name_ar ?? 'محاضرة' }}</div>
                    <div class="list-item-subtitle">
                        {{ \Carbon\Carbon::parse($record->lecture?->date)->format('Y/m/d') }} • {{ $record->lecture?->start_time }}
                    </div>
                </div>
                @php
                    $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'بعذر'];
                    $statusBadges = ['present' => 'badge-success', 'absent' => 'badge-danger', 'late' => 'badge-warning', 'excused' => 'badge-assignment'];
                @endphp
                <span class="badge {{ $statusBadges[$record->status] }}">{{ $statusLabels[$record->status] }}</span>
            </div>
        @endforeach

    @elseif ($role === 'representative')
        {{-- Representative: lectures list + stats --}}
        @php
            $batchStats = app(\App\Services\AttendanceService::class)->batchStats($batch->id);
            $lectures = \App\Models\Lecture::with('course', 'attendances')
                ->where('batch_id', $batch->id)
                ->orderByDesc('date')
                ->paginate(15);
        @endphp

        {{-- Stats summary --}}
        <div class="card" style="background: linear-gradient(135deg, var(--primary), var(--accent)); border: none;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 12px; opacity: 0.8; margin-bottom: 4px;">نسبة الحضور الإجمالية</div>
                    <div style="font-size: 36px; font-weight: 900;">{{ $batchStats['rate'] }}%</div>
                </div>
                <div style="text-align: left;">
                    <div style="font-size: 12px; opacity: 0.8;">{{ $batchStats['lectures'] }} محاضرة</div>
                    <div style="font-size: 12px; opacity: 0.8;">{{ $batchStats['total_records'] }} سجل</div>
                </div>
            </div>
        </div>

        {{-- Create lecture button --}}
        <a href="/mobile/attendance/lectures/create" class="btn btn-primary" style="margin-bottom: 16px;">
            ➕ إنشاء محاضرة جديدة
        </a>

        {{-- Lectures list --}}
        <div class="section-title">المحاضرات</div>
        @foreach ($lectures as $lecture)
            <a href="/mobile/attendance/lectures/{{ $lecture->id }}" class="list-item">
                <div class="list-item-content">
                    <div class="list-item-title">{{ $lecture->course?->name_ar ?? 'محاضرة' }}</div>
                    <div class="list-item-subtitle">
                        {{ \Carbon\Carbon::parse($lecture->date)->format('Y/m/d') }} • {{ $lecture->start_time }}
                                        • {{ $lecture->room ?? '—' }}
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                    @if ($lecture->attendance_locked)
                        <span class="badge badge-warning">🔒 مقفل</span>
                    @else
                        <span class="badge badge-success">🔓 مفتوح</span>
                    @endif
                    <span class="badge badge-general">{{ $lecture->attendances->count() }} طالب</span>
                </div>
            </a>
        @endforeach
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
