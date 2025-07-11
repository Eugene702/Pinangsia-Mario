<?php

namespace App\Exports;

use App\Models\InventoryBorrowing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BorrowingHistoryExport implements FromCollection, WithHeadings, WithMapping
{
    protected $borrowings;

    public function __construct($borrowings = null)
    {
        $this->borrowings = $borrowings ?? InventoryBorrowing::history()->get();
    }

    public function collection()
    {
        return $this->borrowings;
    }

    public function headings(): array
    {
        return [
            'Nama Barang',
            'Jumlah',
            'Peminjam',
            'Tanggal Pinjam',
            'Tanggal Kembali',
            'Status',
            'Catatan'
        ];
    }

    public function map($borrowing): array
    {
        return [
            $borrowing->inventory->name,
            $borrowing->borrowed_quantity,
            $borrowing->user->name,
            $borrowing->borrowed_at->format('d/m/Y H:i'),
            $borrowing->returned_at?->format('d/m/Y H:i') ?? '-',
            $this->getStatusText($borrowing->status),
            $borrowing->notes
        ];
    }

    private function getStatusText($status): string
    {
        return match ($status) {
            'borrowed' => 'Dipinjam',
            'returned' => 'Dikembalikan',
            'damaged' => 'Rusak',
            'lost' => 'Hilang',
            default => $status,
        };
    }
}
