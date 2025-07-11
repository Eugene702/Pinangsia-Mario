<?php

namespace App\Filament\Receptionist\Resources\GuestResource\Pages;

use App\Filament\Receptionist\Resources\GuestResource;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGuest extends EditRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),

            Actions\Action::make('checkOut')
                ->label('Check Out')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('warning')
                ->visible(fn() => $this->record->check_in <= now() && $this->record->check_out >= now())
                ->requiresConfirmation()
                ->action(function () {
                    // Ubah status kamar menjadi perlu dibersihkan
                    if ($this->record->room) {
                        $this->record->room->update(['status' => 'needs_cleaning']);
                    }

                    // Update check_out time jika perlu
                    if ($this->record->check_out > now()) {
                        $this->record->update(['check_out' => now()]);
                    }

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    // Ini akan dijalankan setelah data tamu diupdate
    protected function afterSave(): void
    {
        // Cek apakah kamar berubah, jika ya, update status kamar
        if ($this->record->wasChanged('room_id')) {
            // Update kamar lama menjadi available
            $oldRoomId = $this->record->getOriginal('room_id');
            if ($oldRoomId) {
                $oldRoom = Room::find($oldRoomId);
                if ($oldRoom) {
                    $oldRoom->update(['status' => 'available']);
                }
            }

            // Update kamar baru menjadi occupied
            if ($this->record->room) {
                $this->record->room->update(['status' => 'occupied']);
            }
        }
    }
}
