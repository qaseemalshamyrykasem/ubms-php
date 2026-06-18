@extends('mobile.layouts.app')
@section('title', 'الإعلانات - UBMS')

@php
    $user = auth()->user();
    $batch = $user->representativeProfile?->batch ?? $user->studentProfile?->batch;
    $announcements = \App\Models\Announcement::where('batch_id', $batch?->id)
        ->published()
        ->with('author', 'course', 'attachments')
        ->orderByDesc('is_pinned')
        ->orderByDesc('published_at')
        ->paginate(15);
    $types = ['holiday' => 'عطلة', 'assignment' => 'واجب', 'lecture' => 'محاضرة', 'schedule' => 'جدول', 'general' => 'عام', 'urgent' => 'عاجل', 'emergency' => 'طوارئ', 'meeting' => 'اجتماع', 'important' => 'مهم'];
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">📢 الإعلانات</div>
    <div class="app-bar-actions">
        @if (in_array($user->primaryRole(), ['representative', 'college_admin', 'super_admin']))
            <a href="/mobile/announcements/create" class="icon-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            </a>
        @endif
    </div>
</div>

<div class="main fade-in">
    <form method="GET" action="/mobile/announcements" style="margin-bottom: 16px;">
        <input type="text" name="q" class="form-input" placeholder="🔍 ابحث في الإعلانات..." value="{{ request('q') }}">
    </form>

    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 8px; margin-bottom: 16px;">
        <a href="/mobile/announcements" class="badge {{ !request('type') ? 'badge-urgent' : 'badge-general' }}" style="white-space: nowrap;">الكل</a>
        @foreach ($types as $value => $label)
            <a href="/mobile/announcements?type={{ $value }}" class="badge badge-{{ $value }} {{ request('type') === $value ? 'badge-urgent' : '' }}" style="white-space: nowrap;">{{ $label }}</a>
        @endforeach
    </div>

    @forelse ($announcements as $announcement)
        <a href="/mobile/announcements/{{ $announcement->id }}" class="list-item" style="display: block;">
            <div style="display: flex; align-items: start; gap: 10px;">
                @if ($announcement->is_pinned)
                    <span style="font-size: 16px;">📌</span>
                @endif
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                        <span class="badge badge-{{ $announcement->type }}">{{ $types[$announcement->type] ?? $announcement->type }}</span>
                        @if ($announcement->course)
                            <span class="badge badge-general">{{ $announcement->course->code }}</span>
                        @endif
                    </div>
                    <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">{{ $announcement->title }}</div>
                    <div style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                        {{ \Illuminate\Support\Str::limit(strip_tags($announcement->body), 100) }}
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-top: 8px; font-size: 11px; color: var(--text-muted);">
                        <span>🕒 {{ \Carbon\Carbon::parse($announcement->published_at)->diffForHumans() }}</span>
                        @if ($announcement->attachments->count() > 0)
                            <span>📎 {{ $announcement->attachments->count() }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <p>لا توجد إعلانات</p>
        </div>
    @endforelse

    @if ($announcements->hasPages())
        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 16px;">
            <a href="{{ $announcements->previousPageUrl() }}" class="btn btn-secondary {{ $announcements->onFirstPage() ? 'disabled' : '' }}" style="width: auto;">السابق</a>
            <a href="{{ $announcements->nextPageUrl() }}" class="btn btn-secondary {{ !$announcements->hasMorePages() ? 'disabled' : '' }}" style="width: auto;">التالي</a>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
