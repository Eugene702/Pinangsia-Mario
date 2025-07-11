<?php

namespace App\Filament\Receptionist\Resources\GuestResource\Pages;

use App\Filament\Receptionist\Resources\GuestResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewGuest extends ViewRecord
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('addRequest')
                ->label('Tambah Permintaan')
                ->icon('heroicon-o-bell')
                ->url(fn() => route('filament.resepsionis.resources.permintaan.create', [
                    'guest_id' => $this->record->id,
                    'room_id' => $this->record->room_id,
                ])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Tamu')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),

                        TextEntry::make('phone')
                            ->label('Telepon'),

                        TextEntry::make('email')
                            ->label('Email'),
                    ])
                    ->columns(2),

                Section::make('Informasi Menginap')
                    ->schema([
                        TextEntry::make('room.room_number')
                            ->label('Nomor Kamar'),

                        TextEntry::make('check_in')
                            ->label('Check In')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('check_out')
                            ->label('Check Out')
                            ->dateTime('d M Y, H:i'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->state(function ($record) {
                                if ($record->check_in > now()) {
                                    return 'Akan Datang';
                                } elseif ($record->check_out < now()) {
                                    return 'Sudah Check Out';
                                } else {
                                    return 'Sedang Menginap';
                                }
                            })
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Akan Datang' => 'info',
                                'Sedang Menginap' => 'success',
                                'Sudah Check Out' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}
