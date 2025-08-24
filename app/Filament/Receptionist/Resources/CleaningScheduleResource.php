<?php

namespace App\Filament\Receptionist\Resources;

use App\Filament\Receptionist\Resources\CleaningScheduleResource\Pages;
use App\Models\CleaningSchedule;
use App\Models\Room;
use App\Models\User;
use App\Services\WaNotificationService;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CleaningScheduleResource extends Resource
{
    protected static ?string $model = CleaningSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Jadwal';
    protected static ?string $navigationLabel = 'Jadwal Pembersihan';
    protected static ?string $modelLabel = 'Jadwal Pembersihan';
    protected static ?string $slug = 'jadwal-pembersihan';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('room_id')
                    ->label('Kamar')
                    ->options(function () {
                        return Room::where('status', 'needs_cleaning')
                            ->orWhere('status', 'available')
                            ->pluck('room_number', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $scheduledAt = $get('scheduled_at');
                                $recordId = $get('id');

                                if (!$scheduledAt) {
                                    return;
                                }

                                $scheduledDate = Carbon::parse($scheduledAt)->format('Y-m-d');

                                $query = CleaningSchedule::where('room_id', $value)
                                    ->whereDate('scheduled_at', $scheduledDate);

                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }

                                if ($query->exists()) {
                                    $fail('Kamar ini sudah dijadwalkan untuk dibersihkan di tanggal yang sama.');
                                }
                            };
                        }
                    ]),

                Select::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->options(function () {
                        return User::where('role', 'housekeeping')->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $scheduledAt = $get('scheduled_at');
                                $recordId = $get('id');

                                if (!$scheduledAt) {
                                    return;
                                }

                                $query = CleaningSchedule::where('assigned_to', $value)
                                    ->where('scheduled_at', $scheduledAt);

                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }

                                if ($query->exists()) {
                                    $fail('Staf ini sudah memiliki jadwal pembersihan di jam yang sama.');
                                }
                            };
                        }
                    ]),

                DateTimePicker::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->default(now())
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $assignedTo = $get('assigned_to');
                                $roomId = $get('room_id');
                                $recordId = $get('id');

                                if (!$assignedTo || !$roomId) {
                                    return;
                                }

                                // Validasi untuk kamar (berdasarkan tanggal saja)
                                $scheduledDate = Carbon::parse($value)->format('Y-m-d');
                                $roomQuery = CleaningSchedule::where('room_id', $roomId)
                                    ->whereDate('scheduled_at', $scheduledDate);

                                if ($recordId) {
                                    $roomQuery->where('id', '!=', $recordId);
                                }

                                if ($roomQuery->exists()) {
                                    $fail('Kamar ini sudah dijadwalkan di tanggal yang sama.');
                                }

                                // Validasi untuk staff (berdasarkan jam tepat)
                                $staffQuery = CleaningSchedule::where('assigned_to', $assignedTo)
                                    ->where('scheduled_at', $value);

                                if ($recordId) {
                                    $staffQuery->where('id', '!=', $recordId);
                                }

                                if ($staffQuery->exists()) {
                                    $fail('Staf yang dipilih sudah memiliki jadwal di jam yang sama.');
                                }
                            };
                        }
                    ]),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                    ])
                    ->default('scheduled')
                    ->required(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Catatan tambahan tentang pembersihan')
                    ->columnSpanFull(),
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

                TextColumn::make('assignedStaff.name')
                    ->label('Ditugaskan Kepada')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('scheduled_at')
                    ->label('Dijadwalkan Pada')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('started_at')
                    ->label('Waktu Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label('Waktu Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('cleaning_duration')
                    ->label('Durasi (menit)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
            ])
            ->defaultSort('scheduled_at')
            ->filters([
                Tables\Filters\Filter::make('scheduled_today')
                    ->label('Dijadwalkan Hari Ini')
                    ->query(fn($query) => $query->whereDate('scheduled_at', Carbon::today())),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                    ]),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Ditugaskan Kepada')
                    ->options(function () {
                        return User::where('role', 'housekeeping')->pluck('name', 'id');
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->after(function ($record) {
                    // Send notification after assignment
                    static::sendAssignmentNotification($record);
                }),
                Tables\Actions\DeleteAction::make(),

                Tables\Actions\Action::make('mark_needs_cleaning')
                    ->label('Tandai Perlu Dibersihkan')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(
                        fn(CleaningSchedule $record) =>
                        $record->room && $record->room->status === 'available'
                    )
                    ->action(function (CleaningSchedule $record) {
                        // Update room status
                        if ($record->room) {
                            $record->room->update(['status' => 'needs_cleaning']);
                        }

                        // Update cleaning schedule
                        $record->update([
                            'status' => 'scheduled',
                            'started_at' => null,
                            'completed_at' => null,
                            'cleaning_duration' => null,
                        ]);

                        // Send notification
                        static::sendAssignmentNotification($record);
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
            'index' => Pages\ManageCleaningSchedules::route('/'),
        ];
    }

    public static function sendAssignmentNotification(CleaningSchedule $record): void
    {
        $staff = User::find($record->assigned_to);
        $roomNumber = $record->room->room_number;
        $scheduledTime = $record->scheduled_at->format('d M Y H:i');

        $waService = new WaNotificationService();
        $waService->sendCleaningAssignmentNotification(
            $staff,
            $roomNumber,
            $scheduledTime
        );
    }
}
