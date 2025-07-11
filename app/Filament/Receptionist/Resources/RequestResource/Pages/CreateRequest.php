<?php

namespace App\Filament\Receptionist\Resources\RequestResource\Pages;

use App\Filament\Receptionist\Resources\RequestResource;
use App\Models\Guest;
use App\Models\Room;
use App\Models\User;
use App\Services\WaNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $staff = User::findOrFail($data['assigned_to']);
        $room = Room::findORFail($data['room_id']);
        $guest = Guest::findOrFail($data['guest_id']);


        $waService = new WaNotificationService();
        $message = "🔔 *PERMINTAAN BARU* 🔔\n\n" .
            "Kamar: {$room->room_number}\n" .
            "Tamu: {$guest->name}\n" .
            "Jenis: " . match ($data['type']) {
                'cleaning' => 'Pembersihan',
                'maintenance' => 'Perbaikan',
                'amenities' => 'Perlengkapan',
                default => 'Lainnya'
            } . "\n" .
            "Deskripsi: {$data['description']}\n\n" .
            "Status: Menunggu\n\n" .
            "Silahkan tinjau permintaan ini.";
        $waService->sendMessage($staff->no_telp, $message);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
