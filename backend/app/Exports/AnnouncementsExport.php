<?php

namespace App\Exports;

use App\Models\Announcement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnnouncementsExport implements FromCollection, WithHeadings, WithStyles
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch) {}

    public function collection()
    {
        return Announcement::where('batch_id', $this->batch->id)
            ->with(['author', 'course'])
            ->get()
            ->map(fn ($a) => [
                $a->published_at?->format('Y-m-d H:i'),
                $a->type,
                $a->title,
                mb_substr(strip_tags($a->body), 0, 200),
                $a->course?->name_ar ?? '-',
                $a->author?->name_ar ?? $a->author?->name,
                $a->is_pinned ? 'نعم' : 'لا',
                $a->reads()->count(),
            ]);
    }

    public function headings(): array
    {
        return ['التاريخ', 'النوع', 'العنوان', 'المحتوى', 'المقرر', 'الناشر', 'مثبت', 'عدد القراء'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}
