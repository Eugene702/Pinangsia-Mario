<?php

namespace App\Filament\Receptionist\Resources\CleaningScheduleResource\Pages;

use App\Filament\Receptionist\Resources\CleaningScheduleResource;
use App\Models\CleaningSchedule;
use App\Services\WaNotificationService;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;

class ManageCleaningSchedules extends ManageRecords
{
    protected static string $resource = CleaningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function (CleaningSchedule $record) {
                    CleaningScheduleResource::sendAssignmentNotification($record);
                }),
        ];
    }

    protected function createRecordAndNotify(array $data)
    {
        $record = static::getModel()::create($data);

        try {
            $waService = new WaNotificationService();
            $staff = $record->assignedStaff;

            if (!$staff || !$staff->phone) {
                throw new \Exception('Data staff atau nomor telepon tidak valid');
            }

            $roomNumber = $record->room->room_number;
            $scheduledTime = $record->scheduled_at->format('d M Y H:i');

            $success = $waService->sendCleaningAssignmentNotification(
                $staff,
                $roomNumber,
                $scheduledTime
            );

            if (!$success) {
                Log::warning('Gagal mengirim WA notifikasi untuk jadwal ID: ' . $record->id);
            }

            return $record;
        } catch (\Exception $e) {
            Log::error('Error mengirim notifikasi: ' . $e->getMessage());
            // Tetap return record meski notifikasi gagal
            return $record;
        }
    }
}
