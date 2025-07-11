<?php

namespace App\Filament\Widgets;

use App\Models\CleaningSchedule;
use App\Models\Request;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StaffPerformanceStats extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now();

        // Staf dengan jumlah kamar terbanyak
        $topStaff = CleaningSchedule::selectRaw('assigned_to, COUNT(*) as total')
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->first();

        $topStaffName = $topStaff ? User::find($topStaff->assigned_to)?->name ?? 'Tidak ada' : 'Tidak ada';
        $topStaffCount = $topStaff ? $topStaff->total : 0;

        // Staf tercepat
        $fastestStaff = CleaningSchedule::selectRaw('assigned_to, AVG(cleaning_duration) as avg_duration')
            ->where('status', 'completed')
            ->whereNotNull('cleaning_duration')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->groupBy('assigned_to')
            ->orderBy('avg_duration')
            ->first();

        $fastestStaffName = $fastestStaff ? User::find($fastestStaff->assigned_to)?->name ?? 'Tidak ada' : 'Tidak ada';
        $fastestStaffTime = $fastestStaff ? round($fastestStaff->avg_duration, 1) : 0;

        // Staf dengan permintaan terbanyak
        $mostResponsiveStaff = Request::selectRaw('assigned_to, COUNT(*) as total')
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->first();

        $responsiveStaffName = $mostResponsiveStaff ? User::find($mostResponsiveStaff->assigned_to)?->name ?? 'Tidak ada' : 'Tidak ada';
        $responsiveStaffCount = $mostResponsiveStaff ? $mostResponsiveStaff->total : 0;

        return [
            Stat::make('Staf Terbaik (Jumlah Kamar)', $topStaffName)
                ->description($topStaffCount . ' kamar dibersihkan bulan ini')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, $topStaffCount]),

            Stat::make('Staf Tercepat', $fastestStaffName)
                ->description('Rata-rata ' . $fastestStaffTime . ' menit per kamar')
                ->color('primary')
                ->chart([30, 40, 20, 50, 25, 35, $fastestStaffTime]),

            Stat::make('Staf Responsif', $responsiveStaffName)
                ->description($responsiveStaffCount . ' permintaan diselesaikan')
                ->color('warning')
                ->chart([5, 3, 8, 1, 4, 6, $responsiveStaffCount]),
        ];
    }
}
