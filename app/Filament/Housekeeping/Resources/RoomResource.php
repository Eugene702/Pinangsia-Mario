<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\RoomResource\Pages;
use App\Models\CleaningSchedule;
use App\Models\Room;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationGroup = 'Kamar & Inventaris';
    protected static ?string $navigationLabel = 'Status Kamar';
    protected static ?string $modelLabel = 'Status Kamar';
    protected static ?string $slug = 'status-kamar';

    // Disable create/edit pages for housekeeping staff
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('Nomor Kamar')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'available' => 'success',
                        'occupied' => 'info',
                        'needs_cleaning' => 'warning',
                        'maintenance' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                        default => $state,
                    }),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('room_number')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('start_cleaning')
                    ->label('Mulai Pembersihan')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(fn(Room $record) => $record->status === 'needs_cleaning')
                    ->action(function (Room $record) {
                        // Create a cleaning schedule if one doesn't exist
                        $cleaningSchedule = CleaningSchedule::firstOrCreate(
                            [
                                'room_id' => $record->id,
                                'status' => 'scheduled',
                                'scheduled_at' => Carbon::today(),
                            ],
                            [
                                'assigned_to' => Auth::id(),
                            ]
                        );

                        // Update the schedule to in progress
                        $cleaningSchedule->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);
                    }),

                Tables\Actions\Action::make('complete_cleaning')
                    ->label('Selesai Pembersihan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Room $record) => $record->status === 'needs_cleaning')
                    ->action(function (Room $record) {
                        // Find today's cleaning schedule
                        $cleaningSchedule = CleaningSchedule::where('room_id', $record->id)
                            ->whereDate('scheduled_at', Carbon::today())
                            ->where('assigned_to', Auth::id())
                            ->first();

                        if ($cleaningSchedule) {
                            $cleaningSchedule->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);

                            // Calculate cleaning duration
                            if ($cleaningSchedule->started_at) {
                                $cleaningSchedule->calculateDuration();
                                $cleaningSchedule->save();
                            }
                        }

                        // Update room status
                        $record->update(['status' => 'available']);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRooms::route('/'),
        ];
    }
}
