<?php

namespace App\Filament\Receptionist\Resources;

use App\Filament\Receptionist\Resources\RoomResource\Pages;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Manajemen Kamar';
    protected static ?string $navigationLabel = 'Status Kamar';
    protected static ?string $modelLabel = 'Kamar';
    protected static ?string $pluralModelLabel = 'Kamar';
    protected static ?string $slug = 'status-kamar';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('room_number')
                    ->label('Nomor Kamar')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                    ])
                    ->required()
                    ->helperText('Ubah status kamar sesuai kondisi saat ini'),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Catatan hanya dapat dilihat, tidak dapat diubah'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_number')
                    ->label('Nomor Kamar')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                    ])
                    ->sortable()
                    ->selectablePlaceholder(false),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    }),

                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Jumlah Tamu')
                    ->counts('guests')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                    ]),

                Tables\Filters\Filter::make('has_guests')
                    ->label('Memiliki Tamu')
                    ->query(fn(Builder $query): Builder => $query->whereHas('guests')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah Status')
                    ->modalHeading('Ubah Status Kamar')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('update_status')
                        ->label('Perbarui Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'available' => 'Tersedia',
                                    'occupied' => 'Terisi',
                                    'needs_cleaning' => 'Perlu Dibersihkan',
                                    'maintenance' => 'Dalam Perbaikan',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('room_number', 'asc');
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
            'index' => Pages\ListRooms::route('/'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Receptionist tidak bisa membuat kamar baru
    }

    public static function canDelete($record): bool
    {
        return false; // Receptionist tidak bisa menghapus kamar
    }

    public static function canDeleteAny(): bool
    {
        return false; // Receptionist tidak bisa menghapus kamar secara bulk
    }

    public static function canView($record): bool
    {
        return false; // Tidak ada halaman view, langsung edit
    }
}