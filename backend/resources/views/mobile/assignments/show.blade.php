@extends('mobile.layouts.app')
@section('title', 'تفاصيل الواجب - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/assignments" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">تفاصيل الواجب</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    @php
        $overdue = \Carbon\Carbon::parse($assignment->deadline)->isPast();
    @endphp

    <div class="card">
        @if ($assignment->course)
            <span class="badge badge-general" style="margin-bottom: 8px;">{{ $assignment->course->name_ar }}</span>
        @endif
        <h1 style="font-size: 20px; font-weight: 900; margin: 8px 0 12px; line-height: 1.3;">{{ $assignment->title }}</h1>

        <div style="display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border); flex-wrap: wrap;">
            <div style="background: {{ $overdue ? 'rgba(239, 68, 68, 0.2)' : 'rgba(99, 102, 241, 0.2)' }}; color: {{ $overdue ? '#fca5a5' : '#93c5fd' }}; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">
                ⏰ {{ \Carbon\Carbon::parse($assignment->deadline)->format('Y/m/d - H:i') }}
            </div>
            <div style="background: rgba(245, 158, 11, 0.2); color: #fcd34d; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">
                📊 الدرجة: {{ $assignment->max_grade }}
            </div>
        </div>

        @if ($assignment->description)
            <div style="font-size: 14px; line-height: 1.7; color: var(--text); white-space: pre-wrap;">{{ $assignment->description }}</div>
        @endif
    </div>

    @if ($assignment->attachments->count() > 0)
        <div class="section-title">📎 المرفقات ({{ $assignment->attachments->count() }})</div>
        @foreach ($assignment->attachments as $attachment)
            <a href="/mobile/assignments/{{ $assignment->id }}/attachments/{{ $attachment->id }}/download" class="list-item">
                <div style="width: 40px; height: 40px; background: rgba(99, 102, 241, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;">📄</div>
                <div class="list-item-content" style="margin: 0 12px;">
                    <div class="list-item-title">{{ $attachment->original_name }}</div>
                    <div class="list-item-subtitle">{{ number_format($attachment->file_size / 1024, 1) }} KB</div>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        @endforeach
    @endif

    @if (auth()->user()->id === $assignment->user_id || in_array(auth()->user()->primaryRole(), ['college_admin', 'super_admin']))
        <div style="display: flex; gap: 8px; margin-top: 16px;">
            <a href="/mobile/assignments/{{ $assignment->id }}/edit" class="btn btn-secondary" style="width: auto;">✏️ تعديل</a>
            <form method="POST" action="/mobile/assignments/{{ $assignment->id }}" onsubmit="return confirm('تأكيد الحذف؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="width: auto;">🗑️ حذف</button>
            </form>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
