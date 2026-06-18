@extends('mobile.layouts.app')
@section('title', 'تفاصيل الإعلان - UBMS')

@php
    $types = ['holiday' => 'عطلة', 'assignment' => 'واجب', 'lecture' => 'محاضرة', 'schedule' => 'جدول', 'general' => 'عام', 'urgent' => 'عاجل', 'emergency' => 'طوارئ', 'meeting' => 'اجتماع', 'important' => 'مهم'];
@endphp

@section('content')
<div class="app-bar">
    <a href="/mobile/announcements" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">تفاصيل الإعلان</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <div class="card">
        <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 12px; flex-wrap: wrap;">
            <span class="badge badge-{{ $announcement->type }}">{{ $types[$announcement->type] ?? $announcement->type }}</span>
            @if ($announcement->course)
                <span class="badge badge-general">{{ $announcement->course->name_ar }}</span>
            @endif
            @if ($announcement->is_pinned)
                <span class="badge badge-warning">📌 مثبت</span>
            @endif
        </div>

        <h1 style="font-size: 20px; font-weight: 900; margin-bottom: 12px; line-height: 1.3;">{{ $announcement->title }}</h1>

        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border);">
            <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                {{ mb_substr($announcement->author?->name_ar ?? $announcement->author?->name ?? '؟', 0, 1) }}
            </div>
            <div>
                <div style="font-size: 13px; font-weight: 600;">{{ $announcement->author?->name_ar ?? $announcement->author?->name }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</div>
            </div>
        </div>

        <div style="font-size: 15px; line-height: 1.7; color: var(--text);">
            {{ nl2br(e($announcement->body)) }}
        </div>
    </div>

    @if ($announcement->attachments->count() > 0)
        <div class="section-title">📎 المرفقات ({{ $announcement->attachments->count() }})</div>
        @foreach ($announcement->attachments as $attachment)
            <a href="/mobile/announcements/{{ $announcement->id }}/attachments/{{ $attachment->id }}/download" class="list-item">
                <div style="width: 40px; height: 40px; background: rgba(99, 102, 241, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    📄
                </div>
                <div class="list-item-content" style="margin: 0 12px;">
                    <div class="list-item-title">{{ $attachment->original_name }}</div>
                    <div class="list-item-subtitle">{{ number_format($attachment->file_size / 1024, 1) }} KB</div>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        @endforeach
    @endif

    @if (auth()->user()->primaryRole() === 'student')
        @php
            $isRead = $announcement->reads()->where('user_id', auth()->id())->exists();
        @endphp
        @if (!$isRead)
            <form method="POST" action="/mobile/announcements/{{ $announcement->id }}/mark-read">
                @csrf
                <button type="submit" class="btn btn-outline" style="margin-top: 16px;">✓ وضع علامة كمقروء</button>
            </form>
        @endif
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
