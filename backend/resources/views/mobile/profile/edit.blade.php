@extends('mobile.layouts.app')
@section('title', 'تعديل البيانات - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/profile" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">تعديل البيانات</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <form method="POST" action="/mobile/profile" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group" style="text-align: center; margin-bottom: 24px;">
            <label for="avatar" style="cursor: pointer;">
                <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; color: white; position: relative;">
                    {{ mb_substr($user->name_ar ?? $user->name, 0, 1) }}
                    <div style="position: absolute; bottom: 0; right: 0; width: 28px; height: 28px; background: var(--bg-card); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">📷</div>
                </div>
                <div style="font-size: 12px; color: var(--primary);">تغيير الصورة</div>
            </label>
            <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;">
        </div>

        <div class="form-group">
            <label class="form-label">الاسم (إنجليزي)</label>
            <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}">
        </div>
        <div class="form-group">
            <label class="form-label">الاسم (عربي)</label>
            <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar', $user->name_ar) }}" dir="rtl">
        </div>
        <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-input" value="{{ $user->email }}" disabled>
        </div>
        <div class="form-group">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}">
        </div>

        <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
    </form>
</div>
@endsection
