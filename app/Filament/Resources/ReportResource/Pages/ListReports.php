<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;
    protected static ?string $title = 'Laporan Kinerja Karyawan';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Ekspor PDF')
                ->icon('bi-file-pdf-fill')
                ->action(function () {

                })
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $filterData = $this->tableFilters['created_at'] ?? null;
        $startDate = isset($filterData['date_from']) ? Carbon::parse($filterData['date_from']) : now()->startOfMonth();
        $endDate = isset($filterData['date_to']) ? Carbon::parse($filterData['date_to']) : now()->endOfMonth();
        $query
            ->withCount([
                'cleaningSchedules' => function (Builder $query) use ($startDate, $endDate) {
                    $query->where('status', 'completed')
                        ->whereBetween('created_at', [$startDate, $endDate]);
                }
            ])
            ->withAvg([
                'cleaningSchedules' => function (Builder $query) use ($startDate, $endDate) {
                    $query->where('status', 'completed')
                        ->whereBetween('created_at', [$startDate, $endDate]);
                }
            ], 'cleaning_duration'); // Pastikan nama kolom durasi benar

        return $query;
    }
}
