<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\CleaningScheduleResource\Pages;
use App\Models\CleaningSchedule;
use App\Models\Room;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class CleaningScheduleResource extends Resource
{
    protected static ?string $model = CleaningSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Jadwal';
    protected static ?string $navigationLabel = 'Jadwal Pembersihan';
    protected static ?string $modelLabel = 'Jadwal Pembersihan';
    protected static ?string $slug = 'jadwal-pembersihan';
    protected static ?string $recordTitleAttribute = 'id';

    // Disable create for housekeeping staff (only receptionist can schedule)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DateTimePicker::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->disabled(),

                DateTimePicker::make('started_at')
                    ->label('Waktu Mulai')
                    ->disabled(),

                DateTimePicker::make('completed_at')
                    ->label('Waktu Selesai')
                    ->disabled(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                    ])
                    ->disabled(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Kamar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->defaultSort('scheduled_at')
            ->filters([
                Tables\Filters\Filter::make('scheduled_today')
                    ->label('Dijadwalkan Hari Ini')
                    ->query(fn($query) => $query->whereDate('scheduled_at', Carbon::today())),

                Tables\Filters\Filter::make('my_schedule')
                    ->label('Jadwal Saya')
                    ->query(fn($query) => $query->where('assigned_to', Auth::id())),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('start_cleaning')
                    ->label('Mulai Pembersihan')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(
                        fn(CleaningSchedule $record) =>
                        $record->status === 'scheduled' &&
                            $record->assigned_to === Auth::id()
                    )
                    ->action(function (CleaningSchedule $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);

                        // Update room status
                        if ($record->room) {
                            $record->room->update(['status' => 'needs_cleaning']);
                        }
                    }),

                Tables\Actions\Action::make('complete_cleaning')
                    ->label('Selesai Pembersihan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(
                        fn(CleaningSchedule $record) =>
                        $record->status === 'in_progress' &&
                            $record->assigned_to === Auth::id()
                    )
                    ->form([
                        Textarea::make('notes')
                            ->label('Catatan Penyelesaian')
                            ->placeholder('Tambahkan catatan tentang penyelesaian pembersihan ini')
                    ])
                    ->action(function (CleaningSchedule $record, array $data) {
                        $now = now();
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => $now,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Calculate cleaning duration
                        if ($record->started_at) {
                            $record->calculateDuration();
                            $record->save();
                        }

                        // Update room status
                        if ($record->room) {
                            $record->room->update(['status' => 'available']);
                        }
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCleaningSchedules::route('/'),
        ];
    }
}
