<?php

namespace App\Filament\Housekeeping\Resources\ProcurementRequestResource\Pages;

use App\Filament\Housekeeping\Resources\ProcurementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcurementRequests extends ListRecords
{
    protected static string $resource = ProcurementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
