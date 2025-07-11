<?php

namespace App\Filament\Housekeeping\Widgets;

use App\Models\CleaningSchedule;
use App\Models\Request;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TasksOverview extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Tugas Pembersihan Kamu';
    }

    protected function getStats(): array
    {
        $userId = Auth::id();

        $todaySchedules = CleaningSchedule::where('assigned_to', $userId)
            ->whereDate('scheduled_at', Carbon::today())
            ->count();

        $pendingRequests = Request::where('assigned_to', $userId)
            ->where('status', 'pending')
            ->count();

        $completedToday = CleaningSchedule::where('assigned_to', $userId)
            ->whereDate('completed_at', Carbon::today())
            ->where('status', 'completed')
            ->count();

        return [
            Stat::make('Jadwal Pembersihan Hari Ini', $todaySchedules)
                ->description('Total jadwal pembersihan hari ini')
                ->color('info'),

            Stat::make('Permintaan Tamu', $pendingRequests)
                ->description('Permintaan tamu yang menunggu')
                ->color('warning'),

            Stat::make('Kamar Selesai Dibersihkan', $completedToday)
                ->description('Dibersihkan hari ini')
                ->color('success'),
        ];
    }
}
