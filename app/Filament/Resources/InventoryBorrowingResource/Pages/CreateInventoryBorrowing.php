<?php

namespace App\Filament\Resources\InventoryBorrowingResource\Pages;

use App\Filament\Resources\InventoryBorrowingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryBorrowing extends CreateRecord
{
    protected static string $resource = InventoryBorrowingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
