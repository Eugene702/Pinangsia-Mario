<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\ProcurementRequestResource\Pages;
use App\Models\ProcurementRequest;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProcurementRequestResource extends Resource
{
    protected static ?string $model = ProcurementRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'Permintaan Pengadaan';
    protected static ?string $modelLabel = 'Permintaan Pengadaan';
    protected static ?string $slug = 'permintaan-pengadaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(Auth::user()->id),

                TextInput::make('item_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Barang')
                    ->columnSpanFull(),

                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->label('Jumlah'),

                TextInput::make('unit')
                    ->required()
                    ->maxLength(50)
                    ->label('Satuan (pcs, kg, dll)'),


                Textarea::make('purpose')
                    ->required()
                    ->label('Tujuan Penggunaan')
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Nama Barang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->where('user_id', Auth::user()->id));
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
            'edit' => Pages\EditProcurementRequest::route('/{record}/edit'),
        ];
    }
}
