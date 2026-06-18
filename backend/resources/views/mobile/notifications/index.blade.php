@extends('mobile.layouts.app')
@section('title', 'الإشعارات - UBMS')

@php
    $user = auth()->user();
    $notifications = \App\Models\SiteNotification::where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->paginate(20);
    $unreadCount = \App\Models\SiteNotification::where('user_id', $user->id)->where('is_read', false)->count();
@endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">🔔 الإشعارات {{ $unreadCount > 0 ? "($unreadCount)" : '' }}</div>
    <div class="app-bar-actions">
        @if ($unreadCount > 0)
            <form method="POST" action="/mobile/notifications/read-all" style="display: inline;">
                @csrf
                <button type="submit" class="icon-btn" title="تحديد الكل كمقروء">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
                </button>
            </form>
        @endif
    </div>
</div>

<div class="main fade-in">
    @forelse ($notifications as $notification)
        <div class="card" style="{{ !$notification->is_read ? 'border-color: var(--primary); background: rgba(99, 102, 241, 0.05);' : ''}}">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                @php
                    $icons = ['info' => 'ℹ️', 'success' => '✅', 'warning' => '⚠️', 'danger' => '🚨'];
                    $colors = ['info' => 'badge-general', 'success' => 'badge-success', 'warning' => 'badge-warning', 'danger' => 'badge-danger'];
                @endphp
                <div style="font-size: 24px;">{{ $icons[$notification->type] ?? '🔔' }}</div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: {{ $notification->is_read ? '600' : '700' }}; font-size: 14px; margin-bottom: 4px;">{{ $notification->title }}</div>
                    <div style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 6px;">{{ $notification->body }}</div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 11px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</span>
                        @if (!$notification->is_read)
                            <form method="POST" action="/mobile/notifications/{{ $notification->id }}/read" style="display: inline;">
                                @csrf
                                <button type="submit" style="background: none; border: none; color: var(--primary); font-size: 11px; cursor: pointer; padding: 0;">تحديد كمقروء</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="empty-state">
            <div class="empty-state-icon">🔔</div>
            <p>لا توجد إشعارات</p>
        </div>
    @endforelse

    @if ($notifications->hasPages())
        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 16px;">
            <a href="{{ $notifications->previousPageUrl() }}" class="btn btn-secondary {{ $notifications->onFirstPage() ? 'disabled' : '' }}" style="width: auto;">السابق</a>
            <a href="{{ $notifications->nextPageUrl() }}" class="btn btn-secondary {{ !$notifications->hasMorePages() ? 'disabled' : '' }}" style="width: auto;">التالي</a>
        </div>
    @endif
</div>

@include('mobile.partials.bottom-nav')
@endsection
