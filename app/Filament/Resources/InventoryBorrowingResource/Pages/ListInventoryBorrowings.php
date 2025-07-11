<?php

namespace App\Filament\Resources\InventoryBorrowingResource\Pages;

use App\Filament\Resources\InventoryBorrowingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryBorrowings extends ListRecords
{
    protected static string $resource = InventoryBorrowingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
