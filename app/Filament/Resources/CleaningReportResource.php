<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CleaningReportResource\Pages;
use App\Models\CleaningSchedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Blade;

class CleaningReportResource extends Resource
{
    protected static ?string $model = CleaningSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Manajemen Housekeeping';
    protected static ?string $navigationLabel = 'Laporan Pembersihan';
    protected static ?string $slug = 'laporan-pembersihan';
    protected static ?string $modelLabel = 'Laporan Pembersihan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('report_date')
                    ->label('Filter by Date')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Nomor Kamar')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('assignedStaff.name')
                    ->sortable()
                    ->searchable()
                    ->label('Ditugaskan ke'),
                TextColumn::make('scheduled_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Jadwal Pembersihan'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('started_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cleaning_duration')
                    ->numeric()
                    ->sortable()
                    ->label('Duration (mins)')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('assigned_to')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->label('Staff'),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Filter::make('date_filter')
                    ->form([
                        DatePicker::make('report_date')
                            ->label('Filter by Date')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['report_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('scheduled_at', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['report_date']) {
                            return null;
                        }

                        return 'Filter by date: ' . $data['report_date'];
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export_pdf')
                    ->label('Export Selected to PDF')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($records) {
                        return response()->streamDownload(function () use ($records) {
                            echo Pdf::loadHtml(
                                Blade::render('staff_performance_pdf', ['records' => $records])
                            )->stream();
                        }, 'staff-report-' . now()->format('Y-m-d') . '.pdf');
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCleaningReports::route('/'),
            'create' => Pages\CreateCleaningReport::route('/create'),
            'edit' => Pages\EditCleaningReport::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
