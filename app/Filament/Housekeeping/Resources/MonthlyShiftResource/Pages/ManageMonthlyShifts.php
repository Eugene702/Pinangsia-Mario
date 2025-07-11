<?php

namespace App\Filament\Housekeeping\Resources\MonthlyShiftResource\Pages;

use App\Filament\Housekeeping\Resources\MonthlyShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMonthlyShifts extends ManageRecords
{
    protected static string $resource = MonthlyShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
