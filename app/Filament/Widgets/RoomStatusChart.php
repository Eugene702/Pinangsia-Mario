<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RoomStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Status Kamar';
    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $statusCount = Room::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Pastikan semua status tersedia, bahkan jika nilainya 0
        $allStatuses = [
            'available' => 'Tersedia',
            'occupied' => 'Ditempati',
            'needs_cleaning' => 'Perlu Dibersihkan',
            'cleaned' => 'Sudah Dibersihkan',
        ];

        // Susun data untuk chart
        $labels = [];
        $values = [];

        foreach ($allStatuses as $status => $label) {
            $labels[] = $label;
            $values[] = $statusCount[$status] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kamar',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // hijau untuk tersedia
                        'rgb(239, 68, 68)', // merah untuk ditempati
                        'rgb(234, 179, 8)', // kuning untuk perlu dibersihkan
                        'rgb(59, 130, 246)', // biru untuk sudah dibersihkan
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
