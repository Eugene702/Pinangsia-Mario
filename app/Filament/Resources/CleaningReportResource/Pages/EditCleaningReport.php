<?php

namespace App\Filament\Resources\CleaningReportResource\Pages;

use App\Filament\Resources\CleaningReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCleaningReport extends EditRecord
{
    protected static string $resource = CleaningReportResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
