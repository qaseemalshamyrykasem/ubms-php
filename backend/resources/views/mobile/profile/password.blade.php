@extends('mobile.layouts.app')
@section('title', 'تغيير كلمة المرور - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/profile" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">🔒 تغيير كلمة المرور</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <form method="POST" action="/mobile/profile/password">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">كلمة المرور الحالية</label>
            <input type="password" name="current_password" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">كلمة المرور الجديدة</label>
            <input type="password" name="password" class="form-input" required>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">8 أحرف على الأقل</div>
        </div>
        <div class="form-group">
            <label class="form-label">تأكيد كلمة المرور</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="btn btn-primary">تغيير كلمة المرور</button>
    </form>
</div>
@endsection
