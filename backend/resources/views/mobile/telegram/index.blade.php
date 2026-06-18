@extends('mobile.layouts.app')
@section('title', 'تيليجرام - UBMS')

@php $user = auth()->user(); @endphp

@section('content')
<div class="app-bar">
    <a href="/mobile/profile" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">✈️ تيليجرام</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    @if (session('success'))
        <div style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Status --}}
    <div class="card" style="text-align: center; padding: 24px;">
        @if ($user->telegram_connected)
            <div style="font-size: 48px; margin-bottom: 12px;">✅</div>
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 4px; color: #6ee7b7;">متصل</h2>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 4px;">@{{ $user->telegram_username }}</p>
            @if ($user->telegram_connected_at)
                <p style="color: var(--text-muted); font-size: 11px;">منذ {{ \Carbon\Carbon::parse($user->telegram_connected_at)->diffForHumans() }}</p>
            @endif

            <form method="POST" action="/mobile/telegram/disconnect" style="margin-top: 20px;">
                @csrf
                <button type="submit" class="btn btn-danger" onclick="return confirm('تأكيد فصل الحساب؟')">فصل الحساب</button>
            </form>
        @else
            <div style="font-size: 48px; margin-bottom: 12px;">📱</div>
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">ربط حسابك مع تيليجرام</h2>
            <p style="color: var(--text-muted); font-size: 13px; line-height: 1.6; margin-bottom: 16px;">
                اربط حسابك لتصلك الإشعارات الفورية: الإعلانات الجديدة، الواجبات، تنبيهات الحضور.
            </p>

            <form method="POST" action="/mobile/telegram/generate-code">
                @csrf
                <button type="submit" class="btn btn-primary">🔗 ربط الحساب</button>
            </form>
        @endif
    </div>

    @if (session('code'))
        {{-- Verification code display --}}
        <div class="card" style="text-align: center; background: linear-gradient(135deg, var(--primary), var(--accent)); border: none;">
            <div style="color: white; font-size: 13px; margin-bottom: 8px;">رمز التحقق الخاص بك</div>
            <div style="color: white; font-size: 40px; font-weight: 900; letter-spacing: 8px; font-family: monospace; margin: 12px 0;">
                {{ session('code') }}
            </div>
            <div style="color: rgba(255,255,255,0.8); font-size: 11px; margin-bottom: 16px;">
                ⏱ صالح لمدة 10 دقائق
            </div>

            @if (session('deep_link'))
                <a href="{{ session('deep_link') }}" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white; margin-bottom: 8px;">
                    📲 افتح تيليجرام
                </a>
            @endif

            <div style="color: rgba(255,255,255,0.7); font-size: 11px; margin-top: 12px;">
                أو أرسل الرمز يدوياً إلى البوت
            </div>
        </div>

        {{-- Steps --}}
        <div class="section-title">كيف يعمل؟</div>
        <div class="card">
            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">1</div>
                <div style="font-size: 13px; line-height: 1.5;">اضغط على "افتح تيليجرام" أعلاه</div>
            </div>
            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">2</div>
                <div style="font-size: 13px; line-height: 1.5;">سيفتح تيليجرام ويبدأ البوت تلقائياً</div>
            </div>
            <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">3</div>
                <div style="font-size: 13px; line-height: 1.5;">سيتم تأكيد الربط تلقائياً</div>
            </div>
            <div style="display: flex; gap: 12px;">
                <div style="width: 28px; height: 28px; background: var(--success); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;">4</div>
                <div style="font-size: 13px; line-height: 1.5;">ستبدأ باستقبال الإشعارات الفورية!</div>
            </div>
        </div>
    @endif
</div>
@endsection
