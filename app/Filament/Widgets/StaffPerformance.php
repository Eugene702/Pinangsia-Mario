<?php

namespace App\Filament\Widgets;

use App\Models\CleaningSchedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class StaffPerformance extends ChartWidget
{
    protected static ?string $heading = 'Kinerja Staf Housekeeping';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Ambil semua staf housekeeping
        $staffs = User::where('role', 'housekeeping')->get();

        // Hitung jumlah kamar yang dibersihkan minggu ini untuk setiap staf
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now();

        $labels = [];
        $values = [];

        foreach ($staffs as $staff) {
            $cleaningCount = CleaningSchedule::where('assigned_to', $staff->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
                ->count();

            $labels[] = $staff->name;
            $values[] = $cleaningCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Kamar Dibersihkan (Minggu Ini)',
                    'data' => $values,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
