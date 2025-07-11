<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Guest;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TodayCheckOutsWidget extends BaseWidget
{
    protected static ?string $heading = 'Check-out Hari Ini';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Guest::query()
            ->whereDate('check_out', now()->toDateString())
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Tamu')
                ->searchable()
                ->sortable(),

            TextColumn::make('room.room_number')
                ->label('Kamar')
                ->sortable(),

            TextColumn::make('check_out')
                ->label('Waktu Check-out')
                ->dateTime('d M Y, H:i')
                ->sortable(),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->state(function ($record) {
                    if ($record->check_out < now()) {
                        return 'sudah checkout';
                    } else {
                        return 'belum checkout';
                    }
                })
                ->colors([
                    'success' => 'sudah checkout',
                    'warning' => 'belum checkout',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            // Action::make('view')
            //     ->label('Lihat Detail')
            //     ->url(fn(Guest $record): string => route('filament.receptionist.resources.tamu.view', ['record' => $record])),

            Action::make('checkout')
                ->label('Check-out')
                ->icon('heroicon-m-arrow-right-circle')
                ->color('warning')
                ->visible(fn(Guest $record) => $record->check_out >= now())
                ->requiresConfirmation()
                ->modalHeading('Check Out Tamu')
                ->modalDescription('Yakin ingin melakukan check out? Status kamar akan berubah menjadi "Perlu Dibersihkan".')
                ->action(function (Guest $record) {
                    // Ubah status kamar menjadi perlu dibersihkan
                    if ($record->room) {
                        $record->room->update(['status' => 'needs_cleaning']);
                    }

                    // Update check_out time jika perlu
                    if ($record->check_out > now()) {
                        $record->update(['check_out' => now()]);
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
