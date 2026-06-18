@extends('mobile.layouts.app')
@section('title', 'تفاصيل المحاضرة - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/attendance" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">تفاصيل المحاضرة</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <div class="card" style="text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">📅</div>
        <h2 style="font-size: 18px; font-weight: 700;">{{ $lecture->course?->name_ar ?? 'محاضرة' }}</h2>
        <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">
            {{ \Carbon\Carbon::parse($lecture->date)->format('Y/m/d') }} • {{ $lecture->start_time }}
        </p>
        @if ($lecture->room)
            <p style="color: var(--text-muted); font-size: 12px; margin-top: 4px;">📍 {{ $lecture->room }}</p>
        @endif
        <div style="margin-top: 12px;">
            @if ($lecture->attendance_locked)
                <span class="badge badge-warning">🔒 الحضور مقفل</span>
            @else
                <span class="badge badge-success">🔓 الحضور مفتوح</span>
            @endif
        </div>
    </div>

    @if (auth()->user()->primaryRole() === 'representative')
        {{-- QR Code for the lecture --}}
        <div class="section-title">📷 رمز QR للطلاب</div>
        <div class="card" style="text-align: center; background: white;">
            @php
                $qrPayload = json_encode([
                    'lecture_id' => $lecture->id,
                    'token' => $lecture->qr_token,
                    'exp' => $lecture->qr_expires_at?->timestamp,
                ]);
            @endphp
            <img src="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->margin(1)->generate($qrPayload)) }}" alt="QR" style="width: 220px; height: 220px; margin: 0 auto;">

            @if ($lecture->qr_expires_at)
                <div style="color: #475569; font-size: 12px; margin-top: 12px;">
                    ⏱ ينتهي خلال: {{ $lecture->qr_expires_at->diffForHumans() }}
                </div>
            @endif
        </div>

        <div style="display: flex; gap: 8px; margin-top: 12px;">
            <form method="POST" action="/mobile/attendance/lectures/{{ $lecture->id }}/refresh-qr" style="flex: 1;">
                @csrf
                <button type="submit" class="btn btn-outline">🔄 تحديث QR</button>
            </form>
            @if (!$lecture->attendance_locked)
                <form method="POST" action="/mobile/attendance/lectures/{{ $lecture->id }}/lock" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn btn-danger">🔒 قفل</button>
                </form>
            @endif
        </div>
    @endif

    {{-- Attendees --}}
    <div class="section-title">الحاضرون ({{ $lecture->attendances->count() }})</div>
    @foreach ($lecture->attendances as $attendance)
        @php
            $statusLabels = ['present' => 'حاضر', 'absent' => 'غائب', 'late' => 'متأخر', 'excused' => 'بعذر'];
            $statusBadges = ['present' => 'badge-success', 'absent' => 'badge-danger', 'late' => 'badge-warning', 'excused' => 'badge-assignment'];
        @endphp
        <div class="list-item">
            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px;">
                {{ mb_substr($attendance->student?->name_ar ?? $attendance->student?->name ?? '؟', 0, 1) }}
            </div>
            <div class="list-item-content" style="margin: 0 12px;">
                <div class="list-item-title">{{ $attendance->student?->name_ar ?? $attendance->student?->name }}</div>
                <div class="list-item-subtitle">
                    {{ $attendance->verification_method === 'qr' ? '📷 QR' : '✍️ يدوي' }}
                    @if ($attendance->recorded_at)
                        • {{ \Carbon\Carbon::parse($attendance->recorded_at)->format('H:i') }}
                    @endif
                </div>
            </div>
            <span class="badge {{ $statusBadges[$attendance->status] }}">{{ $statusLabels[$attendance->status] }}</span>
        </div>
    @endforeach

    @if ($lecture->attendances->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">👥</div>
            <p>لا يوجد سجلات حضور بعد</p>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
