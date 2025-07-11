<?php

namespace App\Exports;

use App\Models\MonthlyShift;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShiftExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $month;

    public function __construct($month = null)
    {
        $this->month = $month;
    }

    public function collection()
    {
        $query = MonthlyShift::with('user');

        if ($this->month) {
            $query->where('month', $this->month);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Nama Staff',
            'Bulan',
            'Pola Shift',
            'Shift Custom',
            'Catatan',
            'Dibuat Pada'
        ];
    }

    public function map($shift): array
    {
        return [
            $shift->user->name,
            // Format month to 'F Y' (e.g., January 2023)
            Carbon::parse($shift->month)->translatedFormat('F Y'),
            // Carbon::createFromFormat('Y-m', $shift->month . '-01')->translatedFormat('F Y'),
            $shift->shift_pattern === 'regular' ? 'Regular (Pagi Senin-Jumat)' : 'Custom',
            $shift->shift_pattern === 'custom' ? implode(', ', $shift->shift_data) : '-',
            $shift->notes ?? '-',
            $shift->created_at->format('d/m/Y H:i')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:F' => ['alignment' => ['wrapText' => true]],
            'A1:F1' => ['fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D9E1F2']]]
        ];
    }
}
