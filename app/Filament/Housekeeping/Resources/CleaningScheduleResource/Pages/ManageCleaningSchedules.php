<?php

namespace App\Filament\Housekeeping\Resources\CleaningScheduleResource\Pages;

use App\Filament\Housekeeping\Resources\CleaningScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCleaningSchedules extends ManageRecords
{
    protected static string $resource = CleaningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
