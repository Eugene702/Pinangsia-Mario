<?php

namespace App\Filament\Resources\CleaningReportResource\Pages;

use App\Filament\Resources\CleaningReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCleaningReports extends ListRecords
{
    protected static string $resource = CleaningReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
