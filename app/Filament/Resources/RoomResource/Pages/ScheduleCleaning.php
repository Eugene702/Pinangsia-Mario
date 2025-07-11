<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\RoomResource;
use App\Models\CleaningSchedule;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class ScheduleCleaning extends Page
{
    protected static string $resource = RoomResource::class;

    protected static string $view = 'filament.resources.room-resource.pages.schedule-cleaning';

    public ?array $data = [];

    public Room $record;

    public function mount(Room $record): void
    {
        $this->record = $record;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Pembersihan Baru')
                    ->schema([
                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Dijadwalkan Pada')
                            ->required()
                            ->default(now()->addHour()),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan'),
                    ])
                    ->columns(2),
            ]);
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $schedule = new CleaningSchedule();
        $schedule->room_id = $this->record->id;
        $schedule->assigned_to = $data['assigned_to'];
        $schedule->scheduled_at = $data['scheduled_at'];
        $schedule->notes = $data['notes'] ?? null;
        $schedule->status = 'pending';
        $schedule->save();

        $this->redirect(RoomResource::getUrl('view', ['record' => $this->record]));

        $this->notify('success', 'Jadwal pembersihan berhasil dibuat.');
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Simpan')
                ->submit('create'),

            \Filament\Actions\Action::make('cancel')
                ->label('Batal')
                ->url(RoomResource::getUrl('view', ['record' => $this->record])),
        ];
    }
}
