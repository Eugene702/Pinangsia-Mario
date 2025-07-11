<?php

namespace App\Filament\Resources\ProcurementRequestResource\Pages;

use App\Filament\Resources\ProcurementRequestResource;
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
