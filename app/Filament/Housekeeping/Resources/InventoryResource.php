<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Stok Perlengkapan';
    protected static ?string $modelLabel = 'Stok Perlengkapan';
    protected static ?string $slug = 'stok-perlengkapan';

    // Disable create/edit/delete functionality for housekeeping staff
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Stok Saat Ini')
                    ->color(fn(Inventory $record) => $record->quantity < $record->minimum_stock ? 'danger' : 'success')
                    ->sortable(),

                TextColumn::make('minimum_stock')
                    ->label('Batas Minimum')
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn(Builder $query): Builder => $query->whereColumn('quantity', '<', 'minimum_stock')),
            ])
            ->actions([
                Tables\Actions\Action::make('report_low_stock')
                    ->label('Laporkan Stok Menipis')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn(Inventory $record) => $record->quantity < $record->minimum_stock)
                    ->action(function (Inventory $record) {
                        // Here you could implement a notification to the manager
                        // For now, we'll just update the notes
                        $record->update([
                            'notes' => $record->notes . "\n[" . now()->format('d-m-Y H:i') . "] Stok menipis dilaporkan oleh " . auth()->user()->name
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInventories::route('/'),
        ];
    }
}
