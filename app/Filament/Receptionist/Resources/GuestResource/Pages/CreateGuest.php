<?php

namespace App\Filament\Receptionist\Resources\GuestResource\Pages;

use App\Filament\Receptionist\Resources\GuestResource;
use App\Models\Room;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGuest extends CreateRecord
{
    protected static string $resource = GuestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Ini akan dijalankan setelah data tamu disimpan
    protected function afterCreate(): void
    {
        // Pastikan status kamar diubah menjadi occupied
        $room = Room::find($this->record->room_id);
        if ($room && $room->status === 'available') {
            $room->update(['status' => 'occupied']);
        }
    }
}
