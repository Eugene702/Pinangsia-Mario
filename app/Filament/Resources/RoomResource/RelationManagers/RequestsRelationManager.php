<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected static ?string $title = 'Permintaan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('guest_id')
                    ->label('Tamu')
                    ->relationship('guest', 'name')
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'towel' => 'Handuk Tambahan',
                        'cleaning' => 'Pembersihan Tambahan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'maintenance' => 'Perbaikan',
                        'other' => 'Lainnya',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required(),

                Forms\Components\Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->options(User::where('role', 'staff')->pluck('name', 'id'))
                    ->searchable(),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Diselesaikan Pada'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('guest.name')
                    ->label('Tamu')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'towel' => 'Handuk Tambahan',
                        'cleaning' => 'Pembersihan Tambahan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'maintenance' => 'Perbaikan',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedStaff.name')
                    ->label('Ditugaskan Kepada')
                    ->searchable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Diselesaikan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'towel' => 'Handuk Tambahan',
                        'cleaning' => 'Pembersihan Tambahan',
                        'amenities' => 'Perlengkapan Tambahan',
                        'maintenance' => 'Perbaikan',
                        'other' => 'Lainnya',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status !== 'completed' && $record->status !== 'cancelled'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
