<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير الحضور</title>
    <style>
        @page { size: A4; margin: 2cm 1.5cm; }
        * { font-family: 'DejaVu Sans', 'Noto Sans Arabic', sans-serif; }
        body { color: #1f2937; font-size: 11pt; }
        .header { text-align: center; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #4f46e5; margin: 0; font-size: 18pt; }
        .header h2 { color: #6b7280; margin: 5px 0; font-size: 13pt; font-weight: normal; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10pt; color: #6b7280; }
        .batch-info { background: #f3f4f6; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .batch-info strong { color: #4f46e5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #4f46e5; color: white; padding: 8px; text-align: right; font-size: 10pt; }
        td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; text-align: right; font-size: 10pt; }
        tr:nth-child(even) { background: #f9fafb; }
        .status-present { color: #059669; font-weight: bold; }
        .status-absent { color: #dc2626; font-weight: bold; }
        .status-late { color: #d97706; font-weight: bold; }
        .status-excused { color: #6366f1; font-weight: bold; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; font-size: 9pt; color: #9ca3af; display: flex; justify-content: space-between; }
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-block { text-align: center; width: 30%; }
        .sig-line { margin-top: 40px; border-top: 1px solid #000; padding-top: 5px; font-size: 10pt; }
    </style>
</head>
<body>
    <div class="header">
        @if($university && $university->logo)
            <img src="{{ asset('storage/' . $university->logo) }}" style="height:60px; margin-bottom:8px;" />
        @endif
        <h1>{{ $university?->name_ar ?? 'الجامعة' }}</h1>
        <h2>تقرير الحضور - دفعة {{ $batch->name_ar }} ({{ $batch->code }})</h2>
    </div>

    <div class="meta">
        <span>تاريخ الإصدار: {{ $generatedAt->format('Y-m-d H:i') }}</span>
        <span>عدد السجلات: {{ $attendances->count() }}</span>
    </div>

    <div class="batch-info">
        <strong>المسار:</strong> {{ $batch->chainPath() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>المقرر</th>
                <th>الرقم الجامعي</th>
                <th>اسم الطالب</th>
                <th>الحالة</th>
                <th>الوقت</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $att)
                <tr>
                    <td>{{ $att->lecture?->date?->format('Y-m-d') }}</td>
                    <td>{{ $att->lecture?->course?->code ?? '-' }} - {{ $att->lecture?->course?->name_ar ?? '-' }}</td>
                    <td>{{ $att->batchStudent?->student_id ?? '-' }}</td>
                    <td>{{ $att->student?->name_ar ?? $att->student?->name }}</td>
                    <td class="status-{{ $att->status }}">{{ __('attendance.status_' . $att->status) }}</td>
                    <td>{{ $att->recorded_at?->format('H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-block">
            <div class="sig-line">ممثل الدفعة</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">رئيس القسم</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">عميد الكلية</div>
        </div>
    </div>

    <div class="footer">
        <span>UBMS - نظام إدارة الدفعات الجامعية</span>
        <span>صفحة 1</span>
    </div>
</body>
</html>
