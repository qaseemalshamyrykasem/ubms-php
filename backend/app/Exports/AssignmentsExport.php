<?php

namespace App\Exports;

use App\Models\Assignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssignmentsExport implements FromCollection, WithHeadings, WithStyles
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch) {}

    public function collection()
    {
        return Assignment::where('batch_id', $this->batch->id)
            ->with(['course', 'author'])
            ->get()
            ->map(fn ($a) => [
                $a->created_at?->format('Y-m-d'),
                $a->title,
                $a->course?->name_ar ?? '-',
                $a->deadline?->format('Y-m-d H:i'),
                $a->isOverdue() ? 'منتهي' : 'فعّال',
                $a->max_grade,
                $a->author?->name_ar ?? $a->author?->name,
                $a->attachments()->count(),
            ]);
    }

    public function headings(): array
    {
        return ['التاريخ', 'العنوان', 'المقرر', 'الموعد النهائي', 'الحالة', 'الدرجة', 'الناشر', 'المرفقات'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}
