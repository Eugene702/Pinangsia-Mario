<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Pengguna dan Staff';
    protected static ?string $modelLabel = 'Pengguna dan Staff';
    protected static ?string $slug = 'pengguna';

    protected static int $protectedManagerId = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('role')
                    ->options([
                        'manager' => 'Manager',
                        'receptionist' => 'Resepsionis',
                        'housekeeping' => 'Housekeeping',
                    ])
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('jabatan')
                    ->label('Jabatan')
                    ->maxLength(255),

                TextInput::make('no_telp')
                    ->label('No Telepon')
                    // hint
                    ->hint('Format: 62XXXXXXXXXX')
                    ->tel()
                    ->numeric()
                    ->maxLength(20)
                    ->rule('starts_with:62')
                    ->validationMessages([
                        'starts_with' => 'No Telepon harus diawali dengan 62',
                    ])
                    ->required(),

                Textarea::make('alamat')
                    ->label('Alamat')
                    ->columnSpanFull(),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->dehydrated(fn($state): bool => !empty($state))
                    ->dehydrateStateUsing(fn($state): ?string => $state)
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->label('Jabatan')
                    ->searchable(),
                TextColumn::make('no_telp')
                    ->label('No Telepon')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'manager' => 'gray',
                        'receptionist' => 'warning',
                        'housekeeping' => 'success',
                    })
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'manager' => 'Manager',
                        'receptionist' => 'Resepsionis',
                        'housekeeping' => 'Housekeeping',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsers::route('/'),
        ];
    }
}
