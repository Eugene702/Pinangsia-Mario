<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkSchedulesSeeder extends Seeder
{
    public function run()
    {
        // Ambil semua user dengan role housekeeping
        $housekeepers = User::where('role', 'housekeeping')->get();

        if ($housekeepers->isEmpty()) {
            $this->command->info('Tidak ada housekeeping, buat dulu user dengan role housekeeping!');
            return;
        }

        // Data shift yang tersedia
        $availableShifts = ['pagi', 'siang', 'malam', 'libur'];

        // Buat jadwal untuk 3 bulan: bulan lalu, bulan ini, bulan depan
        $months = [
            now()->subMonth(),
            now(),
            now()->addMonth()
        ];

        foreach ($housekeepers as $housekeeper) {
            foreach ($months as $month) {
                $daysInMonth = $month->daysInMonth;
                $scheduleData = [];

                // Generate jadwal untuk setiap hari dalam bulan
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    // Hari libur (Sabtu/Minggu)
                    $date = Carbon::create($month->year, $month->month, $day);
                    if ($date->isWeekend()) {
                        $scheduleData[$day] = 'libur';
                        continue;
                    }

                    // Rotasi shift untuk hari kerja
                    $shiftIndex = ($day + $housekeeper->id) % 3; // Variasi berdasarkan user ID dan tanggal
                    $scheduleData[$day] = $availableShifts[$shiftIndex];
                }

                // Buat jadwal
                WorkSchedule::create([
                    'user_id' => $housekeeper->id,
                    'month' => $month->format('Y-m-01'),
                    'schedule_data' => $scheduleData,
                    'notes' => 'Jadwal otomatis bulan ' . $month->translatedFormat('F Y')
                ]);
            }
        }

        $this->command->info('Berhasil membuat jadwal kerja bulanan contoh!');
        $this->command->info('Total jadwal dibuat: ' . count($months) * $housekeepers->count());
    }
}
