<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\InventoryBorrowingResource\Pages;
use App\Filament\Housekeeping\Resources\InventoryBorrowingResource\RelationManagers;
use App\Models\Inventory;
use App\Models\InventoryBorrowing;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class InventoryBorrowingResource extends Resource
{
    protected static ?string $model = InventoryBorrowing::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square-stack';
    protected static ?string $modelLabel = 'Pengambilan Barang';
    protected static ?string $pluralModelLabel = 'Pengambilan Barang';
    protected static ?string $navigationLabel = 'Pengambilan Barang';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $slug = 'pengambilan-barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(Auth::user()->id),

                Select::make('inventory_id')
                    ->label('Barang Inventaris')
                    ->options(Inventory::where('status', 'available')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $userId = Auth::user()->id;

                                // Cek apakah user sudah meminjam barang ini dan belum dikembalikan
                                $existingBorrowing = InventoryBorrowing::where('inventory_id', $value)
                                    ->where('user_id', $userId)
                                    ->where('status', 'borrowed')
                                    ->exists();

                                if ($existingBorrowing) {
                                    $fail('Maaf, barang tidak tersedia');
                                }
                            };
                        }
                    ])
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $inventory = Inventory::find($state);
                        if ($inventory) {
                            $set('available_quantity', $inventory->quantity);
                        }
                    }),

                TextInput::make('available_quantity')
                    ->label('Stok Tersedia')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('borrowed_quantity')
                    ->label('Jumlah Dipinjam')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $available = $get('available_quantity');
                                if ($value > $available) {
                                    $fail("Jumlah pinjam tidak boleh melebihi stok tersedia ($available).");
                                }
                            };
                        },
                    ]),

                DateTimePicker::make('borrowed_at')
                    ->label('Waktu Pinjam')
                    ->default(now()),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('user_id', Auth::user()->id))
            ->columns([
                TextColumn::make('inventory.name')
                    ->label('Nama Barang')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('borrowed_quantity')
                    ->label('Jumlah')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('borrowed_at')
                    ->label('Tgl Pinjam')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('returned_at')
                    ->label('Tgl Kembali')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'borrowed' => 'warning',
                        'returned' => 'success',
                        'damaged' => 'danger',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                    ]),

                Filter::make('borrowed_at')
                    ->form([
                        Forms\Components\DatePicker::make('borrowed_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('borrowed_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['borrowed_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('borrowed_at', '>=', $date),
                            )
                            ->when(
                                $data['borrowed_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('borrowed_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('return')
                    ->label('Pengembalian')
                    ->icon('heroicon-o-arrow-up-on-square-stack')
                    ->form([
                        Forms\Components\Select::make('return_status')
                            ->label('Status Pengembalian')
                            ->options([
                                'returned' => 'Dikembalikan',
                                'damaged' => 'Rusak',
                                'lost' => 'Hilang',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('returned_quantity')
                            ->label('Jumlah Dikembalikan')
                            ->numeric()
                            ->required(),
                        // ->rules([
                        //     function ($get) {
                        //         return function (string $attribute, $value, $fail) use ($get) {
                        //             $record = $get('record');
                        //             if ($value > $record->borrowed_quantity) {
                        //                 $fail("Jumlah dikembalikan tidak boleh melebihi jumlah yang dipinjam ({$record->borrowed_quantity}).");
                        //             }
                        //         };
                        //     },
                        // ]),

                        Forms\Components\DateTimePicker::make('returned_at')
                            ->label('Waktu Kembali')
                            ->default(now()),

                        Forms\Components\Textarea::make('return_notes')
                            ->label('Catatan'),
                    ])
                    ->action(function (InventoryBorrowing $record, array $data) {
                        // Update status peminjaman
                        $record->update([
                            'status' => $data['return_status'],
                            'returned_at' => $data['returned_at'],
                            'notes' => $data['return_notes'] ?? $record->notes,
                        ]);

                        $inventory = $record->inventory;

                        // Hanya tambahkan stok jika barang dikembalikan dalam kondisi baik
                        if ($data['return_status'] === 'returned') {
                            $inventory->increment('quantity', $data['returned_quantity']);

                            // Update status barang jika stok tersedia
                            if ($inventory->quantity > 0) {
                                $inventory->update(['status' => 'available']);
                            }
                        }
                    })
                    ->hidden(fn(InventoryBorrowing $record): bool => $record->status !== 'borrowed')
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()
                //         ->label('Hapus yang dipilih'),
                // ]),
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
            'index' => Pages\ListInventoryBorrowings::route('/'),
            'create' => Pages\CreateInventoryBorrowing::route('/create'),
            'edit' => Pages\EditInventoryBorrowing::route('/{record}/edit'),
        ];
    }
}
