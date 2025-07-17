<?php

namespace App\Filament\Resources\CleaningReportResource\Pages;

use App\Filament\Resources\CleaningReportResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListCleaningReports extends ListRecords
{
    protected static string $resource = CleaningReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('export')
                ->label('Ekspor PDF')
                ->icon('bi-file-pdf-fill')
                ->url(fn() => route('report.cleaning-report', [
                    'staff' => $this->tableFilters['assigned_to']['value'] ?? null,
                    'status' => $this->tableFilters['status']['value'] ?? null,
                    'date' => $this->tableFilters['date_filter']['report_date'] ?? null
                ]))
                ->openUrlInNewTab()
        ];
    }
}
