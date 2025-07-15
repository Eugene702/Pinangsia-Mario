<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemPurchaseResource\Pages;
use App\Models\Inventory;
use App\Models\ItemPurchaseTransaction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemPurchaseResource extends Resource
{
    protected static ?string $model = ItemPurchaseTransaction::class;
    protected static ?string $pluralModelLabel = 'Transaksi Pembelian';
    protected static ?string $navigationIcon = 'carbon-purchase';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Pembelian Barang';

    public static function form(Form $form): Form
    {
        $inventory = Inventory::select('id', 'name', 'quantity')
            ->orderBy('name')
            ->get();

        $itemList = $inventory->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->name . ' (' . $item->quantity . ' tersedia)',
            ];
        })->pluck('text', 'id');

        return $form
            ->schema([
                Select::make('inventoryId')
                    ->label('Nama Barang')
                    ->options($itemList),

                TextInput::make('supplier')
                    ->label('Nama Pemasok')
                    ->reactive(),

                TextInput::make('qty')
                    ->label('Jumlah Pembelian')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                TextInput::make('unitPrice')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Textarea::make('note')
                    ->label('Catatan')
                    ->nullable()
                    ->columnSpan(2)
                    ->rows(5)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('supplier')
                    ->label('Nama Pemasok')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('qty')
                    ->label('Jumlah Pembelian')
                    ->sortable(),

                TextColumn::make('unitPrice')
                    ->label('Harga Satuan')
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('note')
                    ->label('Catatan')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('total')
                    ->label('Total Harga')
                    ->state(function ($record) {
                        return $record->qty * $record->unitPrice;
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->sortable()
                    ->date()
            ])
            ->filters([
                Filter::make('created_at')
                    ->label('Tanggal Transaksi')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal')
                            ->required(),

                        DatePicker::make('created_to')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_to'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make('Edit Transaksi'),
                DeleteAction::make('Hapus Transaksi')
                    ->requiresConfirmation()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListItemPurchases::route('/'),
            'create' => Pages\CreateItemPurchase::route('/create'),
            'edit' => Pages\EditItemPurchase::route('/{record}/edit'),
        ];
    }
}
