<?php

namespace App\Filament\Housekeeping\Resources;

use App\Filament\Housekeeping\Resources\RequestResource\Pages;
use App\Models\Request;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Permintaan Tamu';
    protected static ?string $modelLabel = 'Permintaan Tamu';
    protected static ?string $slug = 'permintaan-tamu';

    // Disable creation of new requests for housekeeping staff
    public static function canCreate(): bool
    {
        return false;
    }

    // Only allow limited edits for status updates
    public static function canEdit(Model $record): bool
    {
        // Only allow edit if assigned to this user
        return $record->assigned_to === Auth::id() || $record->assigned_to === null;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Only allow status to be edited
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Ditangani',
                        'completed' => 'Selesai',
                    ])
                    ->required(),

                // Show but disable other fields
                DateTimePicker::make('completed_at')
                    ->label('Waktu Selesai')
                    ->disabled(),

                // Allow notes to be added
                Textarea::make('notes')
                    ->label('Catatan Penyelesaian')
                    ->placeholder('Tambahkan catatan tentang penyelesaian permintaan ini'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Kamar')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('guest.name')
                    ->label('Nama Tamu')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Jenis Permintaan')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 30) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Ditangani',
                        'completed' => 'Selesai',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_today')
                    ->label('Dibuat Hari Ini')
                    ->query(fn($query) => $query->whereDate('created_at', Carbon::today())),

                Tables\Filters\Filter::make('my_requests')
                    ->label('Permintaan Saya')
                    ->query(fn($query) => $query->where('assigned_to', Auth::id())),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Ditangani',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ubah Status')
                    ->visible(
                        fn(Request $record) =>
                        $record->assigned_to === Auth::id() ||
                            $record->assigned_to === null
                    ),

                Tables\Actions\Action::make('start_handling')
                    ->label('Mulai Tangani')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->visible(
                        fn(Request $record) =>
                        $record->status === 'pending' &&
                            ($record->assigned_to === null || $record->assigned_to === Auth::id())
                    )
                    ->action(function (Request $record) {
                        $record->update([
                            'status' => 'in_progress',
                            'assigned_to' => Auth::id(),
                        ]);
                    }),

                Tables\Actions\Action::make('complete_request')
                    ->label('Selesaikan')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(
                        fn(Request $record) =>
                        $record->status === 'in_progress' &&
                            $record->assigned_to === Auth::id()
                    )
                    ->form([
                        Textarea::make('notes')
                            ->label('Catatan Penyelesaian')
                            ->placeholder('Tambahkan catatan tentang penyelesaian permintaan ini'),
                    ])
                    ->action(function (Request $record, array $data) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                            // Add notes to description to maintain history
                            'description' => $record->description . "\n\n[Catatan Penyelesaian: " . ($data['notes'] ?? 'Selesai') . "]",
                        ]);
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }
}
