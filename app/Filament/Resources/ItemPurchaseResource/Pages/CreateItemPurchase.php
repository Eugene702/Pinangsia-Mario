<?php

namespace App\Filament\Resources\ItemPurchaseResource\Pages;

use App\Filament\Resources\ItemPurchaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateItemPurchase extends CreateRecord
{
    protected static string $resource = ItemPurchaseResource::class;

    protected static ?string $title = 'Tambah Transaksi Pembelian Barang';
    protected static ?string $breadcrumb = 'Tambah Transaksi Pembelian Barang';
}
