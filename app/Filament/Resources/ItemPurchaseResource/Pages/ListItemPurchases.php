<?php

namespace App\Filament\Resources\ItemPurchaseResource\Pages;

use App\Filament\Resources\ItemPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemPurchases extends ListRecords
{
    protected static string $resource = ItemPurchaseResource::class;
    protected static ?string $title = 'Transaksi Pembelian Barang';
    protected static ?string $breadcrumb = 'Daftar Transaksi Pembelian Barang';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Transaksi Pembelian'),
        ];
    }
}
