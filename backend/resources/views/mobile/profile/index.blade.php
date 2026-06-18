@extends('mobile.layouts.app')
@section('title', 'حسابي - UBMS')

@php $user = auth()->user(); @endphp

@section('content')
<div class="app-bar">
    <div class="app-bar-title">👤 الملف الشخصي</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    {{-- Profile header --}}
    <div style="text-align: center; margin: 20px 0 24px;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: white;">
            {{ mb_substr($user->name_ar ?? $user->name, 0, 1) }}
        </div>
        <h2 style="font-size: 20px; font-weight: 700;">{{ $user->name_ar ?? $user->name }}</h2>
        <p style="color: var(--text-muted); font-size: 13px;">{{ $user->email }}</p>
        <span class="badge badge-assignment" style="margin-top: 8px;">
            {{ $user->primaryRole() === 'student' ? 'طالب' : ($user->primaryRole() === 'representative' ? 'ممثل الدفعة' : $user->primaryRole()) }}
        </span>
    </div>

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-card" style="text-align: center;">
            <div class="stat-value" style="font-size: 18px;">{{ \Carbon\Carbon::parse($user->created_at)->format('Y/m') }}</div>
            <div class="stat-label">انضم في</div>
        </div>
        <div class="stat-card" style="text-align: center;">
            <div class="stat-value" style="font-size: 18px;">{{ $user->telegram_connected ? '✅' : '❌' }}</div>
            <div class="stat-label">تيليجرام</div>
        </div>
    </div>

    {{-- Profile information --}}
    <div class="section-title">المعلومات الشخصية</div>
    <div class="card">
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);">
            <span style="color: var(--text-muted); font-size: 13px;">الاسم (إنجليزي)</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $user->name }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);">
            <span style="color: var(--text-muted); font-size: 13px;">الاسم (عربي)</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $user->name_ar ?? '—' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);">
            <span style="color: var(--text-muted); font-size: 13px;">البريد</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $user->email }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; padding: 8px 0;">
            <span style="color: var(--text-muted); font-size: 13px;">الهاتف</span>
            <span style="font-size: 13px; font-weight: 500;">{{ $user->phone ?? '—' }}</span>
        </div>
    </div>

    {{-- Menu --}}
    <div class="section-title">الإعدادات</div>
    <a href="/mobile/telegram" class="list-item">
        <div style="font-size: 24px;">✈️</div>
        <div class="list-item-content">
            <div class="list-item-title">ربط تيليجرام</div>
            <div class="list-item-subtitle">{{ $user->telegram_connected ? 'متصل' : 'غير متصل' }}</div>
        </div>
        @if ($user->telegram_connected)
            <span class="badge badge-success">✓</span>
        @endif
    </a>

    <a href="/mobile/profile/edit" class="list-item">
        <div style="font-size: 24px;">✏️</div>
        <div class="list-item-content">
            <div class="list-item-title">تعديل البيانات</div>
            <div class="list-item-subtitle">الاسم، الهاتف، الصورة</div>
        </div>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
    </a>

    <a href="/mobile/profile/password" class="list-item">
        <div style="font-size: 24px;">🔒</div>
        <div class="list-item-content">
            <div class="list-item-title">تغيير كلمة المرور</div>
            <div class="list-item-subtitle">آخر تغيير: غير معروف</div>
        </div>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
    </a>

    {{-- App info --}}
    <div class="section-title">عن التطبيق</div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <div style="font-weight: 700; margin-bottom: 4px;">UBMS Mobile</div>
        <div style="font-size: 12px; color: var(--text-muted);">الإصدار 1.0.0 (NativePHP)</div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">© 2025 University Batch Management System</div>
    </div>

    {{-- Logout --}}
    <form method="POST" action="/mobile/logout" style="margin-top: 16px;">
        @csrf
        <button type="submit" class="btn btn-danger">
            🚪 تسجيل الخروج
        </button>
    </form>
</div>

@include('mobile.partials.bottom-nav')
@endsection
