<?php

namespace App\Filament\Housekeeping\Widgets;

use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoomStatusOverview extends BaseWidget
{
    protected function getHeading(): ?string
    {
        return 'Status Kamar';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Kamar Perlu Dibersihkan', Room::where('status', 'needs_cleaning')->count())
                ->description('Kamar yang menunggu pembersihan')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),

            Stat::make('Kamar Tersedia', Room::where('status', 'available')->count())
                ->description('Kamar yang sudah bersih')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Kamar Terisi', Room::where('status', 'occupied')->count())
                ->description('Kamar yang sedang digunakan')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('warning'),
        ];
    }
}
