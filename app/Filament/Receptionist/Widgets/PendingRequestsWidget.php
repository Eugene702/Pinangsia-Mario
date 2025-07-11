<?php

namespace App\Filament\Receptionist\Widgets;

use App\Models\Request;
use App\Models\User;
use App\Services\WaNotificationService;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PendingRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Permintaan Tamu Yang Belum Selesai';

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Request::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('room.room_number')
                ->label('Kamar')
                ->searchable()
                ->sortable(),

            TextColumn::make('guest.name')
                ->label('Tamu')
                ->searchable()
                ->sortable(),

            TextColumn::make('type')
                ->label('Jenis')
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'cleaning' => 'Pembersihan',
                    'maintenance' => 'Perbaikan',
                    'amenities' => 'Perlengkapan Tambahan',
                    'other' => 'Lainnya',
                    default => $state,
                }),

            TextColumn::make('description')
                ->label('Deskripsi')
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= 30) {
                        return null;
                    }
                    return $state;
                }),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'pending' => 'warning',
                    'in_progress' => 'info',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'pending' => 'Menunggu',
                    'in_progress' => 'Sedang Diproses',
                    default => $state,
                }),

            TextColumn::make('assignedStaff.name')
                ->label('Ditugaskan Kepada')
                ->placeholder('Belum Ditugaskan'),

            TextColumn::make('created_at')
                ->label('Dibuat Pada')
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
                    'pending' => 'Menunggu',
                    'in_progress' => 'Sedang Diproses',
                ]),

            SelectFilter::make('type')
                ->label('Jenis')
                ->options([
                    'cleaning' => 'Pembersihan',
                    'maintenance' => 'Perbaikan',
                    'amenities' => 'Perlengkapan Tambahan',
                    'other' => 'Lainnya',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('assignStaff')
                ->label('Tugaskan')
                ->icon('heroicon-m-user')
                ->color('info')
                ->form([
                    Select::make('assigned_to')
                        ->label('Staf Housekeeping')
                        ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (Request $record, array $data): void {
                    $record->update([
                        'assigned_to' => $data['assigned_to'],
                        'status' => 'in_progress',
                    ]);

                    // Kirim notifikasi WA
                    try {
                        $waService = new WaNotificationService();
                        $staff = User::find($data['assigned_to']);

                        if ($staff && !empty($staff->no_telp)) {
                            $message = "📌 *TUGAS BARU* 📌\n\n" .
                                "Hai {$staff->name},\n\n" .
                                "Anda mendapat tugas baru:\n" .
                                "➡ Kamar: {$record->room->room_number}\n" .
                                "➡ Jenis: " . match ($record->type) {
                                    'cleaning' => 'Pembersihan',
                                    'maintenance' => 'Perbaikan',
                                    'amenities' => 'Perlengkapan',
                                    default => 'Lainnya'
                                } . "\n" .
                                "➡ Permintaan: {$record->description}\n\n" .
                                "Status: Sedang Diproses\n\n" .
                                "Segera tindaklanjuti permintaan ini.";

                            $waService->sendMessage($staff->no_telp, $message);
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal kirim WA notifikasi: ' . $e->getMessage());
                    }
                }),

            Action::make('markAsCompleted')
                ->label('Selesai')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Request $record): void {
                    $record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            BulkAction::make('assignBulk')
                ->label('Tugaskan ke Staf')
                ->icon('heroicon-m-user')
                ->form([
                    Select::make('assigned_to')
                        ->label('Staf Housekeeping')
                        ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(function (Collection $records, array $data): void {
                    foreach ($records as $record) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => 'in_progress',
                        ]);
                    }
                }),

            BulkAction::make('markBulkAsCompleted')
                ->label('Tandai Selesai')
                ->icon('heroicon-m-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->action(function (Collection $records): void {
                    foreach ($records as $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }
                }),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25, 50];
    }
}
