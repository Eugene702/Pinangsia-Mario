<?php

namespace App\Filament\Housekeeping\Resources\InventoryBorrowingResource\Pages;

use App\Filament\Housekeeping\Resources\InventoryBorrowingResource;
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
