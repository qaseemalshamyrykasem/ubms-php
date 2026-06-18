@extends('mobile.layouts.app')
@section('title', 'إنشاء إعلان - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/announcements" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">📢 إنشاء إعلان</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <form method="POST" action="/mobile/announcements" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label class="form-label">العنوان *</label>
            <input type="text" name="title" class="form-input" required autofocus>
        </div>
        <div class="form-group">
            <label class="form-label">النوع *</label>
            <select name="type" class="form-input" required>
                <option value="general">عام</option>
                <option value="urgent">عاجل</option>
                <option value="important">مهم</option>
                <option value="emergency">طوارئ</option>
                <option value="lecture">محاضرة</option>
                <option value="schedule">جدول</option>
                <option value="assignment">واجب</option>
                <option value="holiday">عطلة</option>
                <option value="meeting">اجتماع</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">المقرر</label>
            <select name="course_id" class="form-input">
                <option value="">— لا يوجد —</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">المحتوى *</label>
            <textarea name="body" class="form-input" rows="6" required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">المرفقات</label>
            <input type="file" name="attachments[]" class="form-input" multiple>
        </div>
        <div style="display: flex; gap: 16px; margin: 16px 0;">
            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                <input type="checkbox" name="is_pinned" style="width: 18px; height: 18px;"> 📌 تثبيت
            </label>
            <label style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                <input type="checkbox" name="send_telegram" style="width: 18px; height: 18px;"> ✈️ إرسال لتيليجرام
            </label>
        </div>

        <button type="submit" class="btn btn-primary">📢 نشر الإعلان</button>
    </form>
</div>
@endsection
