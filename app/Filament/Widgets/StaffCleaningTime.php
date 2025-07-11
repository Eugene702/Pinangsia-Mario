<?php

namespace App\Filament\Widgets;

use App\Models\CleaningSchedule;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class StaffCleaningTime extends ChartWidget
{
    protected static ?string $heading = 'Rata-rata Waktu Pembersihan (Menit)';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Ambil semua staf housekeeping
        $staffs = User::where('role', 'housekeeping')->get();

        // Hitung rata-rata waktu pembersihan minggu ini untuk setiap staf
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now();

        $labels = [];
        $values = [];

        foreach ($staffs as $staff) {
            $avgDuration = CleaningSchedule::where('assigned_to', $staff->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$startOfWeek, $endOfWeek])
                ->whereNotNull('cleaning_duration')
                ->avg('cleaning_duration') ?? 0;

            $labels[] = $staff->name;
            $values[] = round($avgDuration, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Waktu (Menit)',
                    'data' => $values,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
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
