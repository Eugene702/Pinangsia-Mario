<?php

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;

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
                ->url(fn() => route('report.employee-performance', [
                    'month' => $this->tableFilters['created_at']['month'] ?? now()->month,
                    'year' => $this->tableFilters['created_at']['year'] ?? now()->year,
                ]))
                ->openUrlInNewTab()
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
        $month = !empty($filterData['month']) ? $filterData['month'] : now()->month;
        $year = !empty($filterData['year']) ? $filterData['year'] : now()->year;

        $query
            ->withCount([
                'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                    $query->where('status', 'completed')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                }
            ])
            ->withAvg([
                'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                    $query->where('status', 'completed')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                }
            ], 'cleaning_duration');

        return $query;
    }
}
