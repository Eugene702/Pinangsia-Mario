<?php

namespace App\Filament\Housekeeping\Pages;

use App\Filament\Housekeeping\Widgets\CleaningScheduleWidget;
use App\Filament\Housekeeping\Widgets\ProcurementTableWidget;
use App\Filament\Housekeeping\Widgets\RoomStatusOverview;
use App\Filament\Housekeeping\Widgets\TasksOverview;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.housekeeping.pages.dashboard';

    public function getTitle(): string|Htmlable
    {
        return "Dashboard";
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TasksOverview::class,
            RoomStatusOverview::class,
            CleaningScheduleWidget::class,
            ProcurementTableWidget::class,
        ];
    }
}
