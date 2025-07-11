<?php

namespace App\Filament\Housekeeping\Resources\InventoryBorrowingResource\Pages;

use App\Filament\Housekeeping\Resources\InventoryBorrowingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryBorrowing extends EditRecord
{
    protected static string $resource = InventoryBorrowingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
