<?php

namespace App\Filament\Housekeeping\Widgets;

use App\Models\CleaningSchedule;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class CleaningScheduleWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    // title
    protected static ?string $heading = 'Jadwal Pembersihan Anda';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CleaningSchedule::query()
                    ->where('assigned_to', Auth::id())
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->orderBy('scheduled_at')
            )
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Kamar')
                    ->sortable(),

                TextColumn::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
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
            ->emptyStateHeading('Tidak ada jadwal pembersihan saat ini');
    }

    public static function canView(): bool
    {
        // Hanya tampilkan untuk staff housekeeping
        return Auth::user()->role == 'housekeeping';
    }
}
