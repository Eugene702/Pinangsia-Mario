<?php

namespace App\Filament\Receptionist\Resources;

use App\Filament\Receptionist\Resources\MonthlyShiftResource\Pages;
use App\Models\MonthlyShift;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonthlyShiftResource extends Resource
{
    protected static ?string $model = MonthlyShift::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Jadwal';
    protected static ?string $modelLabel = 'Jadwal Kerja Staff';
    protected static ?string $navigationLabel = 'Jadwal Kerja Staff';
    protected static ?string $slug = 'jadwal-kerja';

     protected static ?string $pluralModelLabel = 'Jadwal Kerja StaffHousekeeping';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('shift_type')
                    ->label('Tipe Shift')
                    ->description(fn(MonthlyShift $record) => $record->notes),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Filter Bulan')
                    ->options(function () {
                        return MonthlyShift::query()
                            ->selectRaw('DISTINCT month')
                            ->get()
                            ->pluck('month', 'month')
                            ->map(fn($date) => \Carbon\Carbon::parse($date)->translatedFormat('F Y'));
                    })
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Filter Staff')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->searchable()
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMonthlyShifts::route('/'),
        ];
    }
}
