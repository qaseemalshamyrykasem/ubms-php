<?php

namespace App\Exports;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StatisticsExport implements FromArray, WithHeadings, WithTitle, WithStyles
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch) {}

    public function array(): array
    {
        $students = Student::where('batch_id', $this->batch->id)->where('status', 'active')->count();
        $announcements = Announcement::where('batch_id', $this->batch->id)->count();
        $assignments = Assignment::where('batch_id', $this->batch->id)->count();
        $lectures = Lecture::where('batch_id', $this->batch->id)->count();
        $lectureIds = Lecture::where('batch_id', $this->batch->id)->pluck('id');
        $attendances = Attendance::whereIn('lecture_id', $lectureIds)->count();
        $present = Attendance::whereIn('lecture_id', $lectureIds)->whereIn('status', ['present', 'late'])->count();
        $rate = $attendances > 0 ? round(($present / $attendances) * 100, 2) : 0;

        return [
            ['الدفعة', $this->batch->code . ' - ' . $this->batch->name_ar],
            ['عدد الطلاب', $students],
            ['الإعلانات', $announcements],
            ['الواجبات', $assignments],
            ['المحاضرات', $lectures],
            ['سجلات الحضور', $attendances],
            ['الحاضرون', $present],
            ['نسبة الحضور', $rate . '%'],
            ['تاريخ التقرير', now()->format('Y-m-d H:i')],
        ];
    }

    public function headings(): array { return ['المؤشر', 'القيمة']; }

    public function title(): string { return 'إحصائيات الدفعة'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}
