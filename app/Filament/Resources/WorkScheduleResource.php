<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkScheduleResource\Pages;
use App\Models\User;
use App\Models\WorkSchedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jadwal Kerja Staff';
    protected static ?string $modelLabel = 'Jadwal Kerja Staff';
    protected static ?string $slug = 'jadwal-kerja-staff';

    protected static ?string $navigationGroup = 'Jadwal';

    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Housekeeping')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                DatePicker::make('month')
                    ->label('Bulan')
                    ->format('Y-m')
                    ->displayFormat('F Y')
                    ->required(),

                KeyValue::make('schedule_data')
                    ->keyLabel('Tanggal')
                    ->valueLabel('Shift')
                    ->addActionLabel('Tambah Hari')
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Housekeeping'),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('schedule_summary')
                    ->label('Jadwal')
                    ->formatStateUsing(function (WorkSchedule $record) {
                        $shifts = array_count_values($record->schedule_data);
                        return collect($shifts)->map(function ($count, $shift) {
                            return "$count $shift";
                        })->implode(', ');
                    }),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options(function () {
                        return WorkSchedule::query()
                            ->selectRaw('DISTINCT month')
                            ->get()
                            ->pluck('month', 'month')
                            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('F Y'));
                    })
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('calendar')
                    ->icon('heroicon-o-calendar')
                    ->modalContent(function (WorkSchedule $record) {
                        return view('filament.work-schedule-calendar', [
                            'schedule' => $record
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Tables\Actions\DeleteAction::make(),
            ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make()->label('Buat Jadwal'),
            // ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageWorkSchedules::route('/'),
        ];
    }
}
