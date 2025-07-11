<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;

class CleaningSchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'cleaningSchedules';

    protected static ?string $title = 'Jadwal Pembersihan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->required(),

                Forms\Components\Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->options(User::where('role', 'staff')->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->required(),

                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('Diselesaikan Pada'),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedStaff.name')
                    ->label('Ditugaskan Kepada')
                    ->searchable(),

                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Tertunda',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Diselesaikan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
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

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->relationship('assignedStaff', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('start_cleaning')
                    ->label('Mulai Pembersihan')
                    ->icon('heroicon-o-play')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'in_progress',
                            'started_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        // Hitung durasi pembersihan
                        if ($record->started_at) {
                            $duration = now()->diffInMinutes($record->started_at);
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                                'cleaning_duration' => $duration
                            ]);
                        } else {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'in_progress'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
