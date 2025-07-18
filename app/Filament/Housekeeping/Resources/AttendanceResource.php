<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Jadwal';
    protected static ?string $navigationLabel = 'Absensi Kehadiran';
    protected static ?string $pluralModelLabel = 'Absensi Kehadiran';
    protected static ?string $modelLabel = 'Absensi Kehadiran';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('clock_in_time', 'desc')
            ->columns([
                TextColumn::make('clock_in_time')
                    ->label('Clock In')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'tepat_waktu',
                        'danger' => 'terlambat',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucwords(str_replace('_', ' ', $state))),

                TextColumn::make('map_link')
                    ->label('Lokasi')
                    ->default('Lihat di Peta')
                    ->url(function (Attendance $record): ?string {
                        if ($record->clock_in_latitude && $record->clock_in_longitude) {
                        
                            return "https://maps.google.com/?q={$record->clock_in_latitude},{$record->clock_in_longitude}";
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
            ])
            ->filters([
                Filter::make('clock_in_time')
                    ->label('Tanggal Clock In')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('to')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn(Builder $query, $date): Builder => $query->whereDate('clock_in_time', '>=', $date))
                            ->when($data['to'], fn(Builder $query, $date): Builder => $query->whereDate('clock_in_time', '<=', $date));
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
            'index' => Pages\ListAttendances::route('/'),
        ];
    }
}
