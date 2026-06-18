@extends('mobile.layouts.app')
@section('title', 'إنشاء محاضرة - UBMS')

@section('content')
<div class="app-bar">
    <a href="/mobile/attendance" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">إنشاء محاضرة</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in">
    <form method="POST" action="/mobile/attendance/lectures">
        @csrf
        <div class="form-group">
            <label class="form-label">المقرر *</label>
            <select name="course_id" class="form-input" required>
                <option value="">— اختر —</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name_ar }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">عنوان المحاضرة</label>
            <input type="text" name="title" class="form-input" placeholder="اختياري">
        </div>
        <div class="form-group">
            <label class="form-label">التاريخ *</label>
            <input type="date" name="date" class="form-input" required value="{{ date('Y-m-d') }}">
        </div>
        <div class="form-group">
            <label class="form-label">وقت البداية *</label>
            <input type="time" name="start_time" class="form-input" required value="09:00">
        </div>
        <div class="form-group">
            <label class="form-label">وقت النهاية</label>
            <input type="time" name="end_time" class="form-input" value="10:30">
        </div>
        <div class="form-group">
            <label class="form-label">القاعة</label>
            <input type="text" name="room" class="form-input" placeholder="مثلاً: 305">
        </div>

        <button type="submit" class="btn btn-primary">إنشاء المحاضرة</button>
    </form>
</div>
@endsection
