<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Room;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RoomStatusWidget extends BaseWidget
{
    protected static ?string $heading = 'Status Semua Kamar';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Room::query()->latest('updated_at');
    }

    protected function getTableColumns(): array
    {
        return [
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

            TextColumn::make('guests')
                ->label('Tamu Saat Ini')
                ->formatStateUsing(function ($state, $record) {
                    $currentGuests = $record->guests()
                        ->whereDate('check_in', '<=', now())
                        ->whereDate('check_out', '>=', now())
                        ->get();

                    if ($currentGuests->isEmpty()) {
                        return '-';
                    }

                    return $currentGuests->pluck('name')->join(', ');
                }),

            TextColumn::make('cleaningSchedules')
                ->label('Jadwal Pembersihan')
                ->formatStateUsing(function ($state, $record) {
                    $upcomingSchedule = $record->cleaningSchedules()
                        ->where('scheduled_at', '>', now())
                        ->orderBy('scheduled_at')
                        ->first();

                    if (!$upcomingSchedule) {
                        return '-';
                    }

                    return $upcomingSchedule->scheduled_at->format('d M Y, H:i');
                }),

            TextColumn::make('updated_at')
                ->label('Terakhir Diperbarui')
                ->dateTime('d M Y, H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'available' => 'Tersedia',
                    'occupied' => 'Terisi',
                    'needs_cleaning' => 'Perlu Dibersihkan',
                    'maintenance' => 'Dalam Perbaikan',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Action::make('viewRoom')
            //     ->label('Lihat Detail')
            //     ->url(fn(Room $record): string => route('filament.receptionist.resources.kamar.view', ['record' => $record])),

            Action::make('checkIn')
                ->label('Check-in')
                ->icon('heroicon-m-arrow-left-circle')
                ->color('success')
                ->visible(fn(Room $record): bool => $record->status === 'available')
                ->url(fn(Room $record): string => route('filament.receptionist.resources.tamu.create', ['room_id' => $record->id])),

            Action::make('updateStatus')
                ->label('Ubah Status')
                ->icon('heroicon-m-arrow-path')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            'available' => 'Tersedia',
                            'occupied' => 'Terisi',
                            'needs_cleaning' => 'Perlu Dibersihkan',
                            'maintenance' => 'Dalam Perbaikan',
                        ])
                        ->required(),
                ])
                ->action(function (Room $record, array $data): void {
                    $record->update(['status' => $data['status']]);
                }),

            Action::make('requestCleaning')
                ->label('Minta Dibersihkan')
                ->icon('heroicon-m-sparkles')
                ->color('warning')
                ->visible(fn(Room $record): bool => $record->status !== 'needs_cleaning')
                ->requiresConfirmation()
                ->action(function (Room $record) {
                    $record->update(['status' => 'needs_cleaning']);
                }),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
