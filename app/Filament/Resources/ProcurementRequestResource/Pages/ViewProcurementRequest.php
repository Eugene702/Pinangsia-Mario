<?php

namespace App\Filament\Resources\ProcurementRequestResource\Pages;

use App\Filament\Resources\ProcurementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProcurementRequest extends ViewRecord
{
    protected static string $resource = ProcurementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
