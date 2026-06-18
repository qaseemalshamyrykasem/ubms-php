@php
    $unreadCount = auth()->check() ? \App\Models\SiteNotification::where('user_id', auth()->id())->where('is_read', false)->count() : 0;
    $userRole = auth()->user()?->primaryRole();
    $isStudent = $userRole === 'student';
    $isRep = $userRole === 'representative';
@endphp

<nav class="bottom-nav">
    <a href="/mobile/dashboard" class="nav-item {{ request()->is('mobile/dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12L12 3l9 9M5 10v10h14V10"/></svg>
        <span>الرئيسية</span>
    </a>

    <a href="/mobile/announcements" class="nav-item {{ request()->is('mobile/announcements*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-8v18l-18-8M11 19H6v-2"/></svg>
        <span>الإعلانات</span>
    </a>

    <a href="/mobile/attendance" class="nav-item {{ request()->is('mobile/attendance*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        <span>الحضور</span>
    </a>

    <a href="/mobile/assignments" class="nav-item {{ request()->is('mobile/assignments*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
        <span>الواجبات</span>
    </a>

    <a href="/mobile/notifications" class="nav-item {{ request()->is('mobile/notifications*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0"/></svg>
        <span>الإشعارات</span>
        @if($unreadCount > 0)
            <span class="nav-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>
</nav>
