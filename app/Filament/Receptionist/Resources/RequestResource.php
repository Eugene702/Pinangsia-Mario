<?php

namespace App\Filament\Receptionist\Resources;

use App\Filament\Receptionist\Resources\RequestResource\Pages;
use App\Models\Request;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Permintaan Tamu';
    protected static ?string $modelLabel = 'Permintaan Tamu';
    protected static ?string $pluralModelLabel = 'Permintaan Tamu';
    protected static ?string $navigationGroup = 'Manajemen Tamu';
    protected static ?string $slug = 'permintaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('room_id')
                    ->label('Kamar')
                    ->relationship('room', 'room_number')
                    ->searchable()
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $type = $get('type');
                                $recordId = $get('id');

                                // Cek apakah sudah ada request aktif untuk kamar ini
                                $existingRequest = Request::where('room_id', $value)
                                    ->where('type', $type)
                                    ->where('status', '!=', 'completed')
                                    ->when($recordId, function ($query, $id) {
                                        $query->where('id', '!=', $id);
                                    })
                                    ->exists();

                                if ($existingRequest) {
                                    $fail('Sudah ada permintaan aktif untuk kamar ini dengan jenis yang sama.');
                                }
                            };
                        }
                    ]),

                Select::make('guest_id')
                    ->label('Tamu')
                    ->relationship('guest', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Jenis Permintaan')
                    ->options([
                        'cleaning' => 'Pembersihan',
                        'maintenance' => 'Perbaikan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'other' => 'Lainnya',
                    ])
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $roomId = $get('room_id');
                                $recordId = $get('id');

                                if (!$roomId) {
                                    return;
                                }

                                // Cek apakah sudah ada request aktif untuk jenis ini di kamar ini
                                $existingRequest = Request::where('room_id', $roomId)
                                    ->where('type', $value)
                                    ->where('status', '!=', 'completed')
                                    ->when($recordId, function ($query, $id) {
                                        $query->where('id', '!=', $id);
                                    })
                                    ->exists();

                                if ($existingRequest) {
                                    $fail('Sudah ada permintaan aktif untuk jenis ini di kamar yang dipilih.');
                                }
                            };
                        }
                    ]),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Nomor Kamar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('guest.name')
                    ->label('Nama Tamu')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cleaning' => 'Pembersihan',
                        'maintenance' => 'Perbaikan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'other' => 'Lainnya',
                        default => $state,
                    }),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                        default => $state,
                    }),

                TextColumn::make('assignedStaff.name')
                    ->label('Ditugaskan Kepada')
                    ->placeholder('Belum Ditugaskan'),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                    ]),

                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'cleaning' => 'Pembersihan',
                        'maintenance' => 'Perbaikan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'other' => 'Lainnya',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('markCompleted')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Request $record) => $record->status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function (Request $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('assignToStaff')
                        ->label('Tugaskan ke Staf')
                        ->icon('heroicon-o-user')
                        ->form([
                            Select::make('assigned_to')
                                ->label('Staf Housekeeping')
                                ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'assigned_to' => $data['assigned_to'],
                                    'status' => 'in_progress',
                                ]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Tandai Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'completed',
                                    'completed_at' => now(),
                                ]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }
}
