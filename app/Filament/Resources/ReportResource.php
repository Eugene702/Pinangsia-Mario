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
        $month = request()->query('tableFilters')['created_at']['month'] ?? now()->month;
        $year = request()->query('tableFilters')['created_at']['year'] ?? now()->year;

        return parent::getEloquentQuery()
            ->where('role', 'housekeeping')
            ->withCount([
                'cleaningSchedules' => function ($query) use ($month, $year) {
                    $query->where('status', 'completed')
                        ->whereYear('completed_at', $year)
                        ->whereMonth('completed_at', $month);
                }
            ])

            ->withAvg([
                'cleaningSchedules' => function ($query) use ($month, $year) {
                    $query->where('status', 'completed')
                        ->whereYear('completed_at', $year)
                        ->whereMonth('completed_at', $month);
                }
            ], 'cleaning_duration')

            ->withCount([
                'attendance as present_count' => function ($query) use ($month, $year) {
                    $query->whereIn('status', ['tepat_waktu', 'terlambat'])
                        ->whereYear('clock_in_time', $year)
                        ->whereMonth('clock_in_time', $month);
                }
            ]);
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

                TextColumn::make('cleaning_schedules_avg_cleaning_duration')
                    ->label('Durasi Pembersihan')
                    ->formatStateUsing(fn($state) => round($state, 2) . ' menit')
                    ->default(0),

                TextColumn::make('present_count')
                    ->label('Jumlah Hadir')
                    ->default(0),

                TextColumn::make('score')
                    ->label('Skor Kinerja')
                    ->state(function (User $record) {
                        $service = app(\App\Services\PerformanceCalculatorService::class);
                        $score = $service->calculatePerformance($record, $record->present_count);
                        return round($score, 2);
                    })
            ])
            ->defaultSort(function (Builder $query) {
                $service = app(\App\Services\PerformanceCalculatorService::class);
                $targetDuration = $service::TARGET_DURATION;
                $maxRoomsCompleted = $service::MAX_ROOMS_COMPLETED;
                $totalWorkDays = 22; 
    
                $speedWeight = $service::SPEED_WEIGHT;
                $productivityWeight = $service::PRODUCTIVITY_WEIGHT;
                $cleaningWeight = $service::CLEANING_WEIGHT;
                $attendanceWeight = $service::ATTENDANCE_WEIGHT;

                $avgDurationColumn = 'cleaning_schedules_avg_cleaning_duration';
                $countColumn = 'cleaning_schedules_count';
                $presentCountColumn = 'present_count';

                $query->orderByRaw(
                    "CASE 
                    WHEN {$avgDurationColumn} > 0 AND {$maxRoomsCompleted} > 0 AND {$totalWorkDays} > 0 THEN
                        (
                            ( (({$targetDuration} / {$avgDurationColumn}) * 100) * {$speedWeight} ) + 
                            ( (({$countColumn} / {$maxRoomsCompleted}) * 100) * {$productivityWeight} )
                        ) * {$cleaningWeight}
                        +
                        (
                            ( ({$presentCountColumn} / {$totalWorkDays}) * 100 ) * {$attendanceWeight}
                        )
                    ELSE 0 
                END DESC"
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
