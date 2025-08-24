<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers\CleaningSchedulesRelationManager;
use App\Filament\Resources\RoomResource\RelationManagers\GuestsRelationManager;
use App\Filament\Resources\RoomResource\RelationManagers\RequestsRelationManager;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationGroup = 'Manajemen Kamar';
    protected static ?string $navigationLabel = 'Kamar';
    protected static ?string $modelLabel = 'Kamar';
    protected static ?string $slug = 'kamar';

    protected static ?string $pluralModelLabel = 'Daftar Kamar';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('room_number')
                    ->label('Nomor Kamar')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'needs_cleaning' => 'Perlu Dibersihkan',
                        'maintenance' => 'Dalam Perbaikan',
                    ])
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
                    ->sortable(),

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

                // Tables\Columns\TextColumn::make('cleaningSchedules.scheduled_at')
                //     ->label('Jadwal Pembersihan Berikutnya')
                //     ->dateTime('d M Y, H:i')
                //     ->sortable(),

                Tables\Columns\TextColumn::make('guests_count')
                    ->label('Jumlah Tamu')
                    ->counts('guests')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y')
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

                Tables\Filters\Filter::make('has_cleaning_scheduled')
                    ->label('Memiliki Jadwal Pembersihan')
                    ->query(fn(Builder $query): Builder => $query->whereHas('cleaningSchedules')),

                Tables\Filters\Filter::make('has_guests')
                    ->label('Memiliki Tamu')
                    ->query(fn(Builder $query): Builder => $query->whereHas('guests')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('schedule_cleaning')
                //     ->label('Jadwalkan Pembersihan')
                //     ->icon('heroicon-o-calendar')
                //     ->url(fn(Room $record): string => RoomResource::getUrl('schedule-cleaning', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Kamar')
                    ->schema([
                        TextEntry::make('room_number')
                            ->label('Nomor Kamar'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'available' => 'success',
                                'occupied' => 'info',
                                'needs_cleaning' => 'warning',
                                'maintenance' => 'danger',
                            })
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'available' => 'Tersedia',
                                'occupied' => 'Terisi',
                                'needs_cleaning' => 'Perlu Dibersihkan',
                                'maintenance' => 'Dalam Perbaikan',
                                default => $state,
                            }),

                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Jadwal Pembersihan')
                    ->schema([
                        // RelationManagerEntry::make('cleaningSchedules')
                        //     ->label('Riwayat Pembersihan'),
                        RepeatableEntry::make('cleaningSchedules')
                            ->label('Riwayat Pembersihan')
                            ->schema([
                                TextEntry::make('assignedStaff.name')
                                    ->label('Ditugaskan Pada')
                            ])
                    ]),

                Section::make('Tamu Saat Ini')
                    ->schema([
                        // RelationManagerEntry::make('guests')
                        //     ->label('Tamu'),
                    ]),

                Section::make('Permintaan Tamu')
                    ->schema([
                        // RelationManagerEntry::make('requests')
                        //     ->label('Permintaan'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CleaningSchedulesRelationManager::class,
            GuestsRelationManager::class,
            RequestsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'view' => Pages\ViewRoom::route('/{record}'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
            // 'schedule-cleaning' => Pages\ScheduleCleaning::route('/{record}/schedule-cleaning'),
        ];
    }
}
