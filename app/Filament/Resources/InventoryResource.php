<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryResource\Pages;
use App\Models\Inventory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = Inventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Stok Perlengkapan';
    protected static ?string $modelLabel = 'Stok Perlengkapan';
    protected static ?string $slug = 'stok-perlengkapan';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Barang')
                    ->required()
                    ->columnSpanFull()
                    ->unique(ignoreRecord: true)
                    ->placeholder('Masukkan nama barang yang unik')
                    ->hint('Nama barang tidak boleh sama dengan yang sudah ada'),
                TextInput::make('quantity')
                    ->label('Jumlah Stok')
                    ->numeric()
                    ->required(),
                TextInput::make('minimum_stock')
                    ->label('Batas Minimum Stok')
                    ->numeric()
                    ->required(),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->columnSpanFull()
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Stok')
                    ->color(fn(Inventory $record) => $record->quantity < $record->minimum_stock ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('minimum_stock')
                    ->label('Batas Minimum')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis')
                    ->query(fn(Builder $query) => $query->whereColumn('quantity', '<', 'minimum_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('restock')
                    ->label('Restok')
                    ->icon('heroicon-s-arrow-up-circle')
                    ->form([
                        TextInput::make('quantity_added')
                            ->label('Jumlah Ditambahkan')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function (Inventory $record, array $data) {
                        $record->update([
                            'quantity' => $record->quantity + $data['quantity_added']
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit' => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
