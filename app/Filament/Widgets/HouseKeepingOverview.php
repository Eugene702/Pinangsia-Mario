<?php

namespace App\Filament\Widgets;

use App\Models\Request;
use App\Models\Room;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HouseKeepingOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Kamar', Room::count())
                ->description('Total kamar dalam sistem')
                ->descriptionIcon('heroicon-m-home')
                ->color('success'),

            Stat::make('Perlu Dibersihkan', Room::where('status', 'needs_cleaning')->count())
                ->description('Kamar yang menunggu pembersihan')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Permintaan Tertunda', Request::where('status', 'pending')->count())
                ->description('Permintaan tamu yang belum ditangani')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger'),

            Stat::make('Staf Bertugas', User::where('role', 'housekeeping')->count())
                ->description('Jumlah staf housekeeping')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
