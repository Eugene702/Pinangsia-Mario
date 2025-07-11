<?php

namespace App\Filament\Housekeeping\Resources\ProcurementRequestResource\Pages;

use App\Filament\Housekeeping\Resources\ProcurementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProcurementRequest extends CreateRecord
{
    protected static string $resource = ProcurementRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
