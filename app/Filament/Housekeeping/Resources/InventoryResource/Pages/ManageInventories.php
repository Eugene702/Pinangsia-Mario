<?php

namespace App\Filament\Housekeeping\Resources\InventoryResource\Pages;

use App\Filament\Housekeeping\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageInventories extends ManageRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
