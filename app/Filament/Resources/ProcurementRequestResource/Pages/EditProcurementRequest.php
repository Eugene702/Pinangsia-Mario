<?php

namespace App\Filament\Resources\ProcurementRequestResource\Pages;

use App\Filament\Resources\ProcurementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcurementRequest extends EditRecord
{
    protected static string $resource = ProcurementRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
