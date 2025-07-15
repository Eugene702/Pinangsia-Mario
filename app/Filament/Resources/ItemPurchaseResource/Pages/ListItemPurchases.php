<?php

namespace App\Filament\Resources\ItemPurchaseResource\Pages;

use App\Filament\Resources\ItemPurchaseResource;
use Filament\Actions;
use Filament\Actions\Action;
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

            Action::make('export')
                ->label('Ekspor PDF')
                ->icon('bi-file-pdf-fill')
                ->url(fn() => route('report.item-purchase-transaction', [
                    'from' => $this->tableFilters['created_at']['created_from'] ?? null,
                    'to' => $this->tableFilters['created_at']['created_to'] ?? null,
                ]))
                ->openUrlInNewTab()
        ];
    }
}
