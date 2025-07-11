<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Room;
use App\Models\CleaningSchedule;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CleaningStatusOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $needsCleaning = Room::where('status', 'needs_cleaning')->count();
        $scheduledCleaning = CleaningSchedule::where('status', 'scheduled')->count();
        $inProgressCleaning = CleaningSchedule::where('status', 'in_progress')->count();
        $completedToday = CleaningSchedule::where('status', 'completed')
            ->whereDate('completed_at', Carbon::today())
            ->count();

        return [
            Stat::make('Kamar Perlu Dibersihkan', $needsCleaning)
                ->description('Kamar yang menunggu pembersihan')
                ->color('danger')
                ->chart([3, 2, $needsCleaning]),

            Stat::make('Jadwal Dijadwalkan', $scheduledCleaning)
                ->description('Belum dimulai oleh staff')
                ->color('warning')
                ->chart([2, 3, $scheduledCleaning]),

            Stat::make('Sedang Dibersihkan', $inProgressCleaning)
                ->description('Dalam proses pembersihan')
                ->color('info')
                ->chart([1, 2, $inProgressCleaning]),

            Stat::make('Selesai Dibersihkan Hari Ini', $completedToday)
                ->description('Pembersihan selesai hari ini')
                ->color('success')
                ->chart([0, 2, $completedToday]),
        ];
    }
}
