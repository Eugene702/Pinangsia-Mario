<?php

namespace App\Filament\Housekeeping\Resources\AttendanceResource\Pages;

use Afsakar\LeafletMapPicker\LeafletMapPicker;
use App\Filament\Housekeeping\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\MonthlyShift;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;
use Location\Coordinate;
use Location\Distance\Haversine;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clockIn')
                ->label('Absen Sekarang')
                ->icon('heroicon-o-map-pin')
            
                ->visible(fn(): bool => $this->isWithinAttendanceWindow())
                ->form([
                    LeafletMapPicker::make('clock_in_location')
                        ->label('Lokasi Anda')
                        ->height('300px')
                        ->defaultZoom(17)
                        ->myLocationButtonLabel('Dapatkan Lokasi Saat Ini')
                        ->draggable(false)
                        ->clickable(false)
                        ->required(),
                ])
                ->action(function (array $data) {
                
                    if (app()->isLocal()) {
                        $data['clock_in_location'] = [
                            'lat' => config('app.hotel_coordinates.latitude'),
                            'lng' => config('app.hotel_coordinates.longitude'),
                        ];
                    }

                    try {
                        if (empty($data['clock_in_location']) || !isset($data['clock_in_location']['lat'])) {
                            Notification::make()->title('Lokasi Tidak Valid')->danger()->send();
                            return;
                        }

                        $latitude = $data['clock_in_location']['lat'];
                        $longitude = $data['clock_in_location']['lng'];

                        $hotelCoord = new Coordinate(config('app.hotel_coordinates.latitude'), config('app.hotel_coordinates.longitude'));
                        $userCoord = new Coordinate($latitude, $longitude);
                        $distance = (new Haversine())->getDistance($hotelCoord, $userCoord);

                        if ($distance > config('app.attendance_radius')) {
                            throw ValidationException::withMessages([
                                'clock_in_location' => 'Anda berada terlalu jauh dari lokasi hotel.',
                            ]);
                        }

                        $scheduleDetails = $this->getActiveShiftDetails();
                        if (empty($scheduleDetails)) {
                            Notification::make()->title('Jadwal Tidak Ditemukan')->danger()->send();
                            return;
                        }

                        $status = now()->isAfter($scheduleDetails['start']) ? 'terlambat' : 'tepat_waktu';

                        Attendance::create([
                            'user_id' => auth()->id(),
                            'clock_in_time' => now(),
                            'clock_in_latitude' => $latitude,
                            'clock_in_longitude' => $longitude,
                            'status' => $status,
                            'clock_in_location' => $data['clock_in_location'],
                        ]);

                        Notification::make()->title('Absensi berhasil')->success()->send();

                    } catch (\Exception $e) {
                        Notification::make()->title('Terjadi Kesalahan')->danger()->send();
                        dd('error', $e->getMessage());
                        \Log::error('Attendance clock-in failed: ' . $e->getMessage());
                    }
                })
        ];
    }

    public function isWithinAttendanceWindow(): bool
    {
        $scheduleDetails = $this->getActiveShiftDetails();
        if (empty($scheduleDetails)) {
            return false;
        }

        $alreadyClockedIn = Attendance::where('user_id', auth()->id())
            ->where('clock_in_time', '>=', $scheduleDetails['start']->copy()->subMinutes(30))
            ->where('clock_in_time', '<=', $scheduleDetails['end'])
            ->exists();

        if ($alreadyClockedIn) {
            return false;
        }

    
        $startTime = $scheduleDetails['start'];
        $allowedStartTime = $startTime->copy()->subMinutes(30);
        $deadline = $startTime->copy()->addHours(8);             

        return now()->isBetween($allowedStartTime, $deadline);
    }

    public function getActiveShiftDetails(): ?array
    {
        $user = auth()->user();
        $now = now();
        $gracePeriodMinutes = 30;

        $todayMonthlyShift = MonthlyShift::with('monthlyShiftDays')
            ->where('user_id', $user->id)
            ->whereYear('month', $now->year)
            ->whereMonth('month', $now->month)
            ->first();

        if ($todayMonthlyShift) {
            $workDays = $todayMonthlyShift->monthlyShiftDays->pluck('day')->toArray();
            if (in_array($now->dayOfWeekIso, $workDays)) {
                $shiftType = $todayMonthlyShift->shift_data;
                [$startTime, $endTime] = match ($shiftType) {
                    'pagi'  => [today()->setTime(7, 0), today()->setTime(17, 0)],
                    'siang' => [today()->setTime(16, 0), today()->addDay()->setTime(1, 0)],
                    'malam' => [today()->setTime(0, 0), today()->setTime(8, 0)],
                    default => [null, null],
                };

                if ($startTime) {
                    if ($now->isBetween($startTime->copy()->subMinutes($gracePeriodMinutes), $endTime)) {
                        return ['start' => $startTime, 'end' => $endTime];
                    }
                }
            }
        }

        $yesterday = $now->copy()->subDay();
        $yesterdayMonthlyShift = MonthlyShift::with('monthlyShiftDays')
            ->where('user_id', $user->id)
            ->whereYear('month', $yesterday->year)
            ->whereMonth('month', $yesterday->month)
            ->first();

        if ($yesterdayMonthlyShift) {
            $workDays = $yesterdayMonthlyShift->monthlyShiftDays->pluck('day')->toArray();
            if (in_array($yesterday->dayOfWeekIso, $workDays)) {
                $shiftType = $yesterdayMonthlyShift->shift_data;
                if ($shiftType === 'siang') {
                    $startTime = $yesterday->copy()->setTime(16, 0);
                    $endTime = $yesterday->copy()->addDay()->setTime(1, 0);
                    if ($now->isBetween($startTime, $endTime)) {
                        return ['start' => $startTime, 'end' => $endTime];
                    }
                }
            }
        }

        return null;
    }
}