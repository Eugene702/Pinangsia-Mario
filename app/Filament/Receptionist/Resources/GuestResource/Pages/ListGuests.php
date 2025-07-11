<?php

namespace App\Filament\Receptionist\Resources\GuestResource\Pages;

use App\Filament\Receptionist\Resources\GuestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Check In Tamu Baru'),
        ];
    }

    // Filter default untuk menampilkan tamu yang sedang menginap
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('check_in', '<=', now())
            ->where('check_out', '>=', now());
    }
}
