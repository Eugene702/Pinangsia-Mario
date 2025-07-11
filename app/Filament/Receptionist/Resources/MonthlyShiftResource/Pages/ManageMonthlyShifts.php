<?php

namespace App\Filament\Receptionist\Resources\MonthlyShiftResource\Pages;

use App\Filament\Receptionist\Resources\MonthlyShiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMonthlyShifts extends ManageRecords
{
    protected static string $resource = MonthlyShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
