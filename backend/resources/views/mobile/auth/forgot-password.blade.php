@extends('mobile.layouts.app')
@section('title', 'استعادة كلمة المرور - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/login" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">استعادة كلمة المرور</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <div style="text-align: center; margin: 24px 0;">
        <div style="font-size: 48px; margin-bottom: 12px;">🔑</div>
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">نسيت كلمة المرور؟</h2>
        <p style="color: var(--text-muted); font-size: 13px;">أدخل بريدك وسنرسل لك رابط إعادة التعيين</p>
    </div>

    @if (session('status'))
        <div style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; padding: 14px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
            ✅ {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="/mobile/forgot-password">
        @csrf
        <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-input" required autofocus>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="btn btn-primary">إرسال الرابط</button>
    </form>
</div>
@endsection
