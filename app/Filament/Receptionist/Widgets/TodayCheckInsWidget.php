<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Guest;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayCheckInsWidget extends BaseWidget
{
    protected static ?string $heading = 'Check-in Hari Ini';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Guest::query()
            ->whereDate('check_in', now()->toDateString())
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Tamu')
                ->searchable()
                ->sortable(),

            TextColumn::make('phone')
                ->label('Telepon')
                ->searchable(),

            TextColumn::make('room.room_number')
                ->label('Kamar')
                ->sortable(),

            TextColumn::make('check_in')
                ->label('Waktu Check-in')
                ->dateTime('d M Y, H:i')
                ->sortable(),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->state(function ($record) {
                    if ($record->check_in > now()) {
                        return 'menunggu';
                    } else {
                        return 'sedang checkin';
                    }
                })
                ->colors([
                    'warning' => 'menunggu',
                    'success' => 'sedang checkin',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Action::make('view')
            //     ->label('Lihat Detail')
            //     ->url(fn(Guest $record): string => route('filament.receptionist.resources.tamu.view', ['record' => $record])),

            Action::make('confirmCheckIn')
                ->label('Konfirmasi Check-in')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->visible(function (Guest $record) {
                    // Tampilkan hanya jika waktu check-in sudah dekat atau sudah lewat
                    return $record->check_in->diffInHours(now(), false) >= -2;
                })
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Check-in Tamu')
                ->modalDescription('Pastikan tamu telah hadir dan kamar sudah siap')
                ->action(function (Guest $record) {
                    // Pastikan kamar diatur sebagai occupied jika belum
                    if ($record->room && $record->room->status !== 'occupied') {
                        $record->room->update([
                            'status' => 'occupied'
                        ]);
                    }

                    // Update check-in time jika perlu
                    if ($record->check_in > now()) {
                        $record->update([
                            'check_in' => now()
                        ]);
                    }
                }),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25, 50];
    }
}
