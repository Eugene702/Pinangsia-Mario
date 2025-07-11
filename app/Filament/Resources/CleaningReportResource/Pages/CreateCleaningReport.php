<?php

namespace App\Filament\Resources\CleaningReportResource\Pages;

use App\Filament\Resources\CleaningReportResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCleaningReport extends CreateRecord
{
    protected static string $resource = CleaningReportResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
