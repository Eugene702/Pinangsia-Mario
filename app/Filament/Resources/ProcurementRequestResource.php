<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcurementRequestResource\Pages;
use App\Filament\Resources\ProcurementRequestResource\RelationManagers;
use App\Models\ProcurementRequest;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcurementRequestResource extends Resource
{
    protected static ?string $model = ProcurementRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Permintaan Pengadaan';
    protected static ?string $modelLabel = 'Permintaan Pengadaan';
    protected static ?string $slug = 'permintaan-pengadaan';

    // Menambahkan badge untuk menampilkan jumlah pending
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }

    // Warna untuk badge (opsional)
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // warning = kuning, bisa diganti dengan 'danger', 'success', 'info', dll
    }

    // Disable creation of new requests
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Pengajuan')
                    ->schema([
                        TextInput::make('item_name')
                            ->disabled()
                            ->label('Nama Barang'),

                        TextInput::make('quantity')
                            ->disabled()
                            ->numeric()
                            ->label('Jumlah'),

                        TextInput::make('unit')
                            ->disabled()
                            ->label('Satuan'),

                        Textarea::make('purpose')
                            ->disabled()
                            ->label('Tujuan Penggunaan')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->disabled()
                            ->label('Catatan Staff')
                            ->columnSpanFull(),
                    ])->columnSpan(2),

                Section::make('Persetujuan')
                    ->schema([
                        Radio::make('status')
                            ->options([
                                'approved' => 'Setujui',
                                'rejected' => 'Tolak',
                            ])
                            ->required(),

                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(fn(Forms\Get $get): bool => $get('status') === 'rejected')
                            ->hidden(fn(Forms\Get $get): bool => $get('status') !== 'rejected')
                            ->columnSpanFull(),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff Pengaju')
                    ->searchable(),

                TextColumn::make('item_name')
                    ->label('Nama Barang')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Satuan'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Diajukan Pada'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn(ProcurementRequest $record): bool => $record->isPending()),
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
            'index' => Pages\ListProcurementRequests::route('/'),
            'create' => Pages\CreateProcurementRequest::route('/create'),
            'view' => Pages\ViewProcurementRequest::route('/{record}'),
            'edit' => Pages\EditProcurementRequest::route('/{record}/edit'),
        ];
    }
}
