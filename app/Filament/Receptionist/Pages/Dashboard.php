<?php

namespace App\Filament\Receptionist\Pages;

use App\Filament\Receptionist\Widgets\CleaningStatusOverview;
use App\Filament\Receptionist\Widgets\PendingRequestsWidget;
use App\Filament\Receptionist\Widgets\StatsOverview;
use App\Filament\Receptionist\Widgets\TodayCheckInsWidget;
use App\Filament\Receptionist\Widgets\TodayCheckOutsWidget;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.receptionist.pages.dashboard';

    public function getTitle(): string|Htmlable
    {
        return "Dashboard";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            CleaningStatusOverview::class,
            PendingRequestsWidget::class,
            TodayCheckInsWidget::class,
            TodayCheckOutsWidget::class
        ];
    }
}
