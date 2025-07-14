<?php

namespace App\Filament\Resources\ItemPurchaseResource\Pages;

use App\Filament\Resources\ItemPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemPurchase extends EditRecord
{
    protected static string $resource = ItemPurchaseResource::class;
    protected static ?string $title = 'Edit Transaksi Pembelian Barang';
    protected static ?string $breadcrumb = 'Edit Transaksi Pembelian Barang';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Transaksi Pembelian')
                ->requiresConfirmation(),
        ];
    }
}
