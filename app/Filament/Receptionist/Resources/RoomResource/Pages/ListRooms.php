<?php

namespace App\Filament\Receptionist\Resources\RoomResource\Pages;

use App\Filament\Receptionist\Resources\RoomResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada action untuk create karena receptionist tidak bisa membuat kamar baru
        ];
    }

    // Urutkan berdasarkan nomor kamar secara default
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->orderBy('room_number', 'asc');
    }
}