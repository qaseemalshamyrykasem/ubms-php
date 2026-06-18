@extends('mobile.layouts.app')
@section('title', 'إنشاء حساب - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/login" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">إنشاء حساب</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 18px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 28px;">🎓</div>
        <h1 style="font-size: 22px; font-weight: 700;">انضم لدفعتك</h1>
    </div>

    <form method="POST" action="/mobile/register">
        @csrf
        <div class="form-group">
            <label class="form-label">الاسم (إنجليزي) *</label>
            <input type="text" name="name" class="form-input" required value="{{ old('name') }}">
        </div>
        <div class="form-group">
            <label class="form-label">الاسم (عربي)</label>
            <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar') }}" dir="rtl">
        </div>
        <div class="form-group">
            <label class="form-label">البريد الإلكتروني *</label>
            <input type="email" name="email" class="form-input" required value="{{ old('email') }}">
        </div>
        <div class="form-group">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
        </div>
        <div class="form-group">
            <label class="form-label">الدفعة</label>
            <select name="batch_id" class="form-input">
                <option value="">— اختر —</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->code }} - {{ $batch->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">الرقم الجامعي</label>
            <input type="text" name="student_id" class="form-input" value="{{ old('student_id') }}">
        </div>
        <div class="form-group">
            <label class="form-label">كلمة المرور *</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <div class="form-group">
            <label class="form-label">تأكيد كلمة المرور *</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>

        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #fca5a5; padding: 12px 16px; border-radius: 12px; margin-bottom: 16px; font-size: 13px;">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
    </form>
</div>
@endsection
