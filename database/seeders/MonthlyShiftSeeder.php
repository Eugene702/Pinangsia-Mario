<?php

namespace Database\Seeders;

use App\Models\MonthlyShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonthlyShiftSeeder extends Seeder
{
    public function run()
    {
        $housekeepers = User::where('role', 'housekeeping')->get();

        if ($housekeepers->isEmpty()) {
            $this->command->info('Tidak ada staff housekeeping! Buat dulu.');
            return;
        }

        $months = [
            now()->format('Y-m-01'),
            now()->addMonth()->format('Y-m-01')
        ];

        foreach ($housekeepers as $index => $hk) {
            foreach ($months as $month) {
                $pattern = $index % 2 === 0 ? 'regular' : 'custom';

                MonthlyShift::create([
                    'user_id' => $hk->id,
                    'month' => $month,
                    'shift_pattern' => $pattern,
                    'shift_data' => $pattern === 'custom'
                        ? ['pagi', 'malam']
                        : null,
                    'notes' => 'Jadwal otomatis ' . Carbon::parse($month)->translatedFormat('F Y')
                ]);
            }
        }

        $this->command->info('Berhasil membuat jadwal bulanan untuk ' . count($housekeepers) . ' staff');
    }
}
