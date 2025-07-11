<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Guest;
use App\Models\Request;
use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Hitung data untuk statistik
        $availableRooms = Room::where('status', 'available')->count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $needsCleaningRooms = Room::where('status', 'needs_cleaning')->count();
        $maintenanceRooms = Room::where('status', 'maintenance')->count();

        $activeRequests = Request::whereIn('status', ['pending', 'in_progress'])->count();

        $checkInsToday = Guest::whereDate('check_in', now()->toDateString())->count();
        $checkOutsToday = Guest::whereDate('check_out', now()->toDateString())->count();

        return [
            Stat::make('Kamar Tersedia', $availableRooms)
                ->description('Siap untuk check-in')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Kamar Terisi', $occupiedRooms)
                ->description('Sedang ditempati tamu')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Perlu Dibersihkan', $needsCleaningRooms)
                ->description('Menunggu housekeeping')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Dalam Perbaikan', $maintenanceRooms)
                ->description('Tidak dapat digunakan')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('danger'),

            Stat::make('Check-in Hari Ini', $checkInsToday)
                ->description('Tamu yang dijadwalkan tiba')
                ->descriptionIcon('heroicon-m-arrow-left-circle')
                ->color('success'),

            Stat::make('Check-out Hari Ini', $checkOutsToday)
                ->description('Tamu yang dijadwalkan pergi')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color('warning'),

            Stat::make('Permintaan Aktif', $activeRequests)
                ->description('Belum diselesaikan')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
