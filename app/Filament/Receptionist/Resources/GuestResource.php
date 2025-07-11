<?php

namespace App\Filament\Receptionist\Resources;

use App\Filament\Receptionist\Resources\GuestResource\Pages;
use App\Models\Guest;
use App\Models\Request;
use App\Models\Room;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuestResource extends Resource
{
    protected static ?string $model = Guest::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Tamu';
    protected static ?string $modelLabel = 'Tamu';
    protected static ?string $pluralModelLabel = 'Tamu';
    protected static ?string $navigationGroup = 'Manajemen Tamu';
    protected static ?string $slug = 'tamu';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Tamu')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Informasi Kamar')
                    ->schema([
                        Select::make('room_id')
                            ->label('Kamar')
                            ->options(function () {
                                // Hanya tampilkan kamar yang tersedia atau kamar yang saat ini ditempati tamu ini
                                return Room::where('status', 'available')
                                    ->orWhereHas('guests', function (Builder $query) {
                                        $query->where('id', request()->route('record'));
                                    })
                                    ->pluck('room_number', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Auto update room status menjadi occupied saat dipilih
                                if ($state) {
                                    $room = Room::find($state);
                                    if ($room && $room->status === 'available') {
                                        $room->update(['status' => 'occupied']);
                                    }
                                }
                            }),

                        DateTimePicker::make('check_in')
                            ->label('Check In')
                            ->required()
                            ->default(now()),

                        DateTimePicker::make('check_out')
                            ->label('Check Out')
                            ->required()
                            ->default(now()->addDays(1)),

                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('room.room_number')
                    ->label('Nomor Kamar')
                    ->sortable(),

                TextColumn::make('check_in')
                    ->label('Check In')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('requests_count')
                    ->label('Jumlah Permintaan')
                    ->counts('requests')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Sedang Menginap',
                        'upcoming' => 'Akan Datang',
                        'past' => 'Sudah Check Out',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value']) {
                            'active' => $query->where('check_in', '<=', now())->where('check_out', '>=', now()),
                            'upcoming' => $query->where('check_in', '>', now()),
                            'past' => $query->where('check_out', '<', now()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                // ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Action::make('check_out')
                    ->label('Check Out')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn(Guest $record) => $record->check_in <= now() && $record->check_out >= now())
                    ->requiresConfirmation()
                    ->modalHeading('Check Out Tamu')
                    ->modalDescription('Yakin ingin melakukan check out? Status kamar akan berubah menjadi "Perlu Dibersihkan".')
                    ->action(function (Guest $record) {
                        // Ubah status kamar menjadi perlu dibersihkan
                        if ($record->room) {
                            $record->room->update(['status' => 'needs_cleaning']);
                        }

                        // Update check_out time jika perlu
                        if ($record->check_out > now()) {
                            $record->update(['check_out' => now()]);
                        }
                    }),

                Action::make('add_request')
                    ->label('Tambah Permintaan')
                    ->icon('heroicon-o-bell')
                    ->color('gray')
                    ->visible(fn(Guest $record) => $record->check_in <= now() && $record->check_out >= now())
                    ->form([
                        Select::make('type')
                            ->label('Jenis Permintaan')
                            ->options([
                                'cleaning' => 'Pembersihan',
                                'maintenance' => 'Perbaikan',
                                'amenities' => 'Perlengkapan Tambahan',
                                'other' => 'Lainnya',
                            ])
                            ->required(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required(),
                    ])
                    ->action(function (Guest $record, array $data) {
                        // Buat permintaan baru dari tamu
                        Request::create([
                            'room_id' => $record->room_id,
                            'guest_id' => $record->id,
                            'type' => $data['type'],
                            'description' => $data['description'],
                            'status' => 'pending',
                        ]);
                    }),
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
            'index' => Pages\ListGuests::route('/'),
            'create' => Pages\CreateGuest::route('/create'),
            // 'view' => Pages\ViewGuest::route('/{record}'),
            'edit' => Pages\EditGuest::route('/{record}/edit'),
        ];
    }
}
