@extends('mobile.layouts.app')
@section('title', 'الجدول الأسبوعي - UBMS')

@php
    $user = auth()->user();
    $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
    $schedules = \App\Models\Schedule::with('course')
        ->where('batch_id', $batch?->id)
        ->orderByRaw("FIELD(day, 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday')")
        ->orderBy('start_time')
        ->get()
        ->groupBy('day');
    $dayNames = ['sunday' => 'الأحد', 'monday' => 'الإثنين', 'tuesday' => 'الثلاثاء', 'wednesday' => 'الأربعاء', 'thursday' => 'الخميس', 'friday' => 'الجمعة', 'saturday' => 'السبت'];
    $today = strtolower(now()->englishDayOfWeek);
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">📅 الجدول الأسبوعي</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    @foreach ($dayNames as $day => $label)
        @php $items = $schedules->get($day, collect()); @endphp
        @if ($items->count() > 0)
            <div class="section-title" style="display: flex; align-items: center; gap: 8px;">
                {{ $label }}
                @if ($day === $today)
                    <span class="badge badge-success">اليوم</span>
                @endif
                <span style="color: var(--text-muted); font-size: 12px; font-weight: 400;">({{ $items->count() }} محاضرة)</span>
            </div>

            @foreach ($items as $schedule)
                <div class="card" style="{{ $day === $today ? 'border-color: var(--primary);' : '' }}">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="background: linear-gradient(135deg, var(--primary), var(--accent)); color: white; padding: 8px 10px; border-radius: 10px; text-align: center; min-width: 70px;">
                            <div style="font-size: 12px; font-weight: 600;">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</div>
                            <div style="font-size: 10px; opacity: 0.8; margin-top: 2px;">إلى</div>
                            <div style="font-size: 12px; font-weight: 600;">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</div>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">{{ $schedule->course?->name_ar }}</div>
                            <div style="display: flex; gap: 12px; font-size: 12px; color: var(--text-muted); flex-wrap: wrap;">
                                @if ($schedule->room)
                                    <span>📍 {{ $schedule->room }}</span>
                                @endif
                                @if ($schedule->building)
                                    <span>🏢 {{ $schedule->building }}</span>
                                @endif
                            </div>
                            @if ($schedule->instructor_name)
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">👨‍🏫 {{ $schedule->instructor_name }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endforeach

    @if ($schedules->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📅</div>
            <p>لا يوجد جدول أسبوعي بعد</p>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
