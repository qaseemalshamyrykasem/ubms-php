<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Lecture;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements WithMultipleSheets
{
    public function __construct(public $batch, public $filters = []) {}

    public function sheets(): array
    {
        return [
            new AttendanceSummarySheet($this->batch, $this->filters),
            new AttendanceDetailSheet($this->batch, $this->filters),
        ];
    }
}

class AttendanceSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch, public $filters = []) {}

    public function collection()
    {
        $lectures = Lecture::with(['course', 'attendances'])
            ->where('batch_id', $this->batch->id)
            ->when($this->filters['course_id'] ?? null, fn($q, $cid) => $q->where('course_id', $cid))
            ->when($this->filters['from'] ?? null, fn($q, $from) => $q->where('date', '>=', $from))
            ->when($this->filters['to'] ?? null, fn($q, $to) => $q->where('date', '<=', $to))
            ->orderBy('date')
            ->get();

        $data = collect();
        foreach ($lectures as $lec) {
            $present = $lec->attendances->where('status', 'present')->count() + $lec->attendances->where('status', 'late')->count();
            $absent = $lec->attendances->where('status', 'absent')->count();
            $excused = $lec->attendances->where('status', 'excused')->count();
            $total = $lec->attendances->count();
            $data->push([
                $lec->date->format('Y-m-d'),
                $lec->course?->code ?? '-',
                $lec->course?->name_ar ?? '-',
                $lec->start_time,
                $lec->room ?? '-',
                $total,
                $present,
                $absent,
                $excused,
                $total > 0 ? round(($present / $total) * 100, 1) . '%' : '0%',
            ]);
        }
        return $data;
    }

    public function headings(): array
    {
        return ['التاريخ', 'كود المقرر', 'اسم المقرر', 'الوقت', 'القاعة', 'الإجمالي', 'حاضر', 'غائب', 'بعذر', 'نسبة الحضور'];
    }

    public function title(): string { return 'ملخص الحضور'; }

    public function columnWidths(): array
    {
        return ['B' => 18, 'C' => 20, 'D' => 35, 'E' => 12, 'F' => 12, 'G' => 12, 'H' => 12, 'I' => 12, 'J' => 14];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}

class AttendanceDetailSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch, public $filters = []) {}

    public function collection()
    {
        $lectureIds = Lecture::where('batch_id', $this->batch->id)
            ->when($this->filters['course_id'] ?? null, fn($q, $cid) => $q->where('course_id', $cid))
            ->when($this->filters['from'] ?? null, fn($q, $from) => $q->where('date', '>=', $from))
            ->when($this->filters['to'] ?? null, fn($q, $to) => $q->where('date', '<=', $to))
            ->pluck('id');

        return Attendance::with(['student', 'lecture.course', 'batchStudent'])
            ->whereIn('lecture_id', $lectureIds)
            ->get()
            ->map(fn ($a) => [
                $a->lecture?->date?->format('Y-m-d'),
                $a->lecture?->course?->code ?? '-',
                $a->batchStudent?->student_id ?? '-',
                $a->student?->name_ar ?? $a->student?->name,
                $a->status,
                $a->verification_method,
                $a->recorded_at?->format('Y-m-d H:i'),
                $a->notes,
            ]);
    }

    public function headings(): array
    {
        return ['التاريخ', 'المقرر', 'الرقم الجامعي', 'اسم الطالب', 'الحالة', 'طريقة التسجيل', 'وقت التسجيل', 'ملاحظات'];
    }

    public function title(): string { return 'تفاصيل الحضور'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}
