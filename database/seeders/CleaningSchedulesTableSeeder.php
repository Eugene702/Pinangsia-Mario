<?php

namespace Database\Seeders;

use App\Models\CleaningSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CleaningSchedulesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get cleaning staff IDs
        $cleaners = User::where('role', 'cleaner')->pluck('id')->toArray();
        $numCleaners = count($cleaners);

        if ($numCleaners === 0) {
            $this->command->info('No cleaning staff found. Please run UsersTableSeeder first.');
            return;
        }

        // Get room IDs from database
        $roomIds = \App\Models\Room::pluck('id')->toArray();

        if (empty($roomIds)) {
            $this->command->info('No rooms found in the database. Please make sure rooms exist before running this seeder.');
            return;
        }

        // Create cleaning schedules for each room
        $schedules = [];

        // Past completed cleanings
        foreach ($roomIds as $index => $roomId) {
            $cleanerId = $cleaners[$index % $numCleaners];
            $scheduledAt = Carbon::now()->subDays(rand(1, 5))->setHour(9)->setMinute(0);
            $startedAt = (clone $scheduledAt)->addMinutes(rand(0, 30));
            $completedAt = (clone $startedAt)->addMinutes(rand(20, 45));

            $schedules[] = [
                'room_id' => $roomId,
                'assigned_to' => $cleanerId,
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'cleaning_duration' => $startedAt->diffInMinutes($completedAt),
                'notes' => 'Regular cleaning completed',
                'status' => 'completed',
            ];
        }

        // Today's cleanings
        foreach ($roomIds as $index => $roomId) {
            $cleanerId = $cleaners[$index % $numCleaners];
            $scheduledAt = Carbon::today()->setHour(11)->setMinute(0);

            // Some in progress, some pending, some completed
            $status = ['pending', 'in_progress', 'completed'][rand(0, 2)];
            $startedAt = null;
            $completedAt = null;
            $duration = null;
            $notes = 'Regular daily cleaning';

            if ($status === 'in_progress') {
                $startedAt = (clone $scheduledAt)->addMinutes(rand(0, 60));
                $notes = 'Cleaning in progress';
            } elseif ($status === 'completed') {
                $startedAt = (clone $scheduledAt)->addMinutes(rand(0, 30));
                $completedAt = (clone $startedAt)->addMinutes(rand(20, 45));
                $duration = $startedAt->diffInMinutes($completedAt);
                $notes = 'Regular cleaning completed';
            }

            $schedules[] = [
                'room_id' => $roomId,
                'assigned_to' => $cleanerId,
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'cleaning_duration' => $duration,
                'notes' => $notes,
                'status' => $status,
            ];
        }

        // Future scheduled cleanings
        foreach ($roomIds as $index => $roomId) {
            $cleanerId = $cleaners[$index % $numCleaners];
            $scheduledAt = Carbon::tomorrow()->setHour(9)->setMinute(0);

            $schedules[] = [
                'room_id' => $roomId,
                'assigned_to' => $cleanerId,
                'scheduled_at' => $scheduledAt,
                'started_at' => null,
                'completed_at' => null,
                'cleaning_duration' => null,
                'notes' => 'Scheduled cleaning',
                'status' => 'scheduled',
            ];

            // Add one more future cleaning for each room
            $scheduledAt = Carbon::now()->addDays(rand(2, 5))->setHour(9)->setMinute(0);

            $schedules[] = [
                'room_id' => $roomId,
                'assigned_to' => $cleanerId,
                'scheduled_at' => $scheduledAt,
                'started_at' => null,
                'completed_at' => null,
                'cleaning_duration' => null,
                'notes' => 'Scheduled cleaning',
                'status' => 'scheduled',
            ];
        }

        // Insert all schedules
        foreach ($schedules as $schedule) {
            CleaningSchedule::create($schedule);
        }
    }
}
