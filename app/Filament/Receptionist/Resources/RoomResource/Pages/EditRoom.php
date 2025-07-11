<?php

namespace App\Filament\Receptionist\Resources\RoomResource\Pages;

use App\Filament\Receptionist\Resources\RoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRoom extends EditRecord
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali ke Daftar')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Status kamar berhasil diperbarui')
            ->body("Status kamar {$this->record->room_number} telah diubah menjadi {$this->getStatusLabel($this->record->status)}.");
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Tersedia',
            'occupied' => 'Terisi',
            'needs_cleaning' => 'Perlu Dibersihkan',
            'maintenance' => 'Dalam Perbaikan',
            default => $status,
        };
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hanya izinkan perubahan status, field lain tidak boleh diubah
        return [
            'status' => $data['status'],
        ];
    }
}