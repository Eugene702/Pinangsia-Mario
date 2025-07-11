<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\HouseKeepingOverview;
use App\Filament\Widgets\InventoryStatus;
use App\Filament\Widgets\RoomStatusChart;
use App\Filament\Widgets\StaffCleaningTime;
use App\Filament\Widgets\StaffPerformance;
use App\Filament\Widgets\StaffPerformanceStats;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getTitle(): string|Htmlable
    {
        return "Dashboard";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            HouseKeepingOverview::class,
            StaffPerformanceStats::class,
            StaffPerformance::class,
            StaffCleaningTime::class,
            RoomStatusChart::class,
            InventoryStatus::class,
        ];
    }
}
