<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    use \Maatwebsite\Excel\Concerns\Exportable;

    public function __construct(public $batch) {}

    public function collection()
    {
        return Student::where('batch_id', $this->batch->id)
            ->with('user')
            ->get()
            ->map(fn ($s) => [
                $s->student_id,
                $s->user->name_ar ?? $s->user->name,
                $s->user->name,
                $s->user->email,
                $s->user->phone ?? '-',
                $s->user->telegramConnected() ? 'نعم' : 'لا',
                $s->status,
                $s->enrolled_at?->format('Y-m-d'),
            ]);
    }

    public function headings(): array
    {
        return ['الرقم الجامعي', 'الاسم (عربي)', 'الاسم (إنجليزي)', 'البريد الإلكتروني', 'الهاتف', 'تيليجرام', 'الحالة', 'تاريخ التسجيل'];
    }

    public function title(): string { return 'الطلاب'; }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F46E5']]],
        ];
    }
}
