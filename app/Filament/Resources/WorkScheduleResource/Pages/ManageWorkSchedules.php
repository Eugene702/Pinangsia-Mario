<?php

namespace App\Filament\Resources\WorkScheduleResource\Pages;

use App\Filament\Resources\WorkScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageWorkSchedules extends ManageRecords
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
