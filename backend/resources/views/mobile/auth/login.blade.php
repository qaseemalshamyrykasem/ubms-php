@extends('mobile.layouts.app')
@section('title', 'تسجيل الدخول - UBMS')

@section('content')
<div class="main" style="padding-bottom: 24px;">
    <div style="text-align: center; margin: 40px 0 32px;">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 24px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 40px; box-shadow: 0 12px 24px rgba(99, 102, 241, 0.3);">🎓</div>
        <h1 style="font-size: 28px; font-weight: 900; margin-bottom: 8px;">UBMS</h1>
        <p style="color: var(--text-muted);">نظام إدارة الدفعات الجامعية</p>
    </div>

    <form method="POST" action="/mobile/login" style="margin-top: 16px;">
        @csrf
        <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-input" placeholder="you@example.com" required value="{{ old('email') }}" autofocus>
        </div>

        <div class="form-group">
            <label class="form-label">كلمة المرور</label>
            <input type="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin: 16px 0;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted);">
                <input type="checkbox" name="remember" style="width: 18px; height: 18px;">
                تذكرني
            </label>
            <a href="/mobile/forgot-password" style="color: var(--primary); text-decoration: none; font-size: 13px;">نسيت كلمة المرور؟</a>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
            تسجيل الدخول
        </button>
    </form>

    <div style="text-align: center; margin-top: 24px; font-size: 13px; color: var(--text-muted);">
        ليس لديك حساب؟
        <a href="/mobile/register" style="color: var(--primary); font-weight: 600; text-decoration: none;">إنشاء حساب</a>
    </div>

    @if (app()->environment('local'))
    <div style="margin-top: 32px; padding: 12px; background: var(--bg-card); border-radius: 12px; font-size: 11px; color: var(--text-muted); border: 1px solid var(--border);">
        <strong>حسابات تجريبية:</strong><br>
        • admin@ubms.local / password<br>
        • rep@ubms.local / password<br>
        • student1@ubms.local / password
    </div>
    @endif
</div>

<script>
    // Auto-trigger native biometric authentication if available
    if (window.nativephp) {
        // NativePHP Mobile exposes biometric prompt
        setTimeout(() => {
            window.nativephp?.postMessage(JSON.stringify({ type: 'biometric.check' }));
        }, 500);
    }
</script>
@endsection
