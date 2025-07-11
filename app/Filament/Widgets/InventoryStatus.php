<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class InventoryStatus extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Status Persediaan';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Ambil stok yang rendah terlebih dahulu
                Inventory::query()
                    ->orderByRaw('quantity <= minimum_stock DESC')
                    ->orderBy('quantity', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Item')
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable(),

                Tables\Columns\TextColumn::make('minimum_stock')
                    ->label('Stok Minimum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if ($record->quantity <= $record->minimum_stock) {
                            return 'Perlu Restock';
                        }

                        if ($record->quantity <= $record->minimum_stock * 1.5) {
                            return 'Stok Rendah';
                        }

                        return 'Stok Cukup';
                    })
                    ->color(function ($state): string {
                        return match ($state) {
                            'Perlu Restock' => 'danger',
                            'Stok Rendah' => 'warning',
                            'Stok Cukup' => 'success',
                        };
                    }),
            ])
            // ->defaultSort('status')
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Restock')
                    ->icon('heroicon-o-plus')
                    ->visible(fn($record) => $record->quantity <= $record->minimum_stock * 1.5)
                    ->url(fn($record) => route('filament.manager.resources.stok-perlengkapan.edit', ['record' => $record])),
            ]);
    }
}
