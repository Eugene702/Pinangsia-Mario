<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Laporan Kinerja';
    protected static ?string $navigationGroup = 'Laporan';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', '=', 'housekeeping');
    }

    public static function table(Table $table): Table
    {
        $performanceCalculatorService = app(\App\Services\PerformanceCalculatorService::class);
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('ranking')
                    ->label('Peringkat')
                    ->rowIndex(),

                TextColumn::make('name')
                    ->label('Nama Staff'),

                TextColumn::make('totalRoomDone')
                    ->label('Total Kamar Dibersihkan')
                    ->state(function ($record) {
                        return $record->cleaning_schedules_count ?? 0;
                    }),

                TextColumn::make('cleaningDuration')
                    ->label('Durasi Pembersihan')
                    ->state(function ($record) {
                        return $record->cleaning_schedules_avg_cleaning_duration ?? 0;
                    }),

                TextColumn::make('score')
                    ->label('Skor Kinerja')
                    ->state(function ($record) use ($performanceCalculatorService) {
                        return $performanceCalculatorService->calculatePerformance($record);
                    })
            ])
            ->defaultSort(function (Builder $query) use ($performanceCalculatorService) {
                $targetDuration = $performanceCalculatorService::TARGET_DURATION;
                $maxRoomsCompleted = $performanceCalculatorService::MAX_ROOMS_COMPLETED;
                $speedWeight = $performanceCalculatorService::SPEED_WEIGHT;
                $productivityWeight = $performanceCalculatorService::PRODUCTIVITY_WEIGHT;
                $avgDurationColumn = 'cleaning_schedules_avg_cleaning_duration';
                $countColumn = 'cleaning_schedules_count';

                $query->orderByRaw(
                    "CASE WHEN {$avgDurationColumn} > 0 AND {$maxRoomsCompleted} > 0 THEN 
                    ((({$targetDuration} / {$avgDurationColumn}) * 100) * {$speedWeight}) + 
                    ((({$countColumn} / {$maxRoomsCompleted}) * 100) * {$productivityWeight}) 
                    ELSE 0 END DESC"
                );
            })
            ->filters([
                Filter::make('created_at')
                    ->form([
                        Select::make('month')
                            ->label('Bulan')
                            ->options([
                                '1' => 'Januari',
                                '2' => 'Februari',
                                '3' => 'Maret',
                                '4' => 'April',
                                '5' => 'Mei',
                                '6' => 'Juni',
                                '7' => 'Juli',
                                '8' => 'Agustus',
                                '9' => 'September',
                                '10' => 'Oktober',
                                '11' => 'November',
                                '12' => 'Desember',
                            ])
                            ->required(),

                        Select::make('year')
                            ->label('Tahun')
                            ->options(array_combine(
                                range(now()->year, now()->year - 5),
                                range(now()->year, now()->year - 5)
                            ))
                            ->default(now()->year)
                            ->required(),
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
        ];
    }
}
