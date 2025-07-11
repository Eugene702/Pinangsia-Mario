<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GuestsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get room IDs from database
        $roomIds = Room::pluck('id')->toArray();

        if (empty($roomIds)) {
            $this->command->info('No rooms found in the database. Please make sure rooms exist before running this seeder.');
            return;
        }

        // Guest names and details
        $guestDetails = [
            [
                'name' => 'John Smith',
                'phone' => '+62812345678',
                'email' => 'john.smith@email.com',
                'check_in' => Carbon::now()->subDays(2),
                'check_out' => Carbon::now()->addDays(3),
            ],
            [
                'name' => 'Maria Garcia',
                'phone' => '+62823456789',
                'email' => 'maria.garcia@email.com',
                'check_in' => Carbon::now()->subDays(1),
                'check_out' => Carbon::now()->addDays(4),
            ],
            [
                'name' => 'David Chen',
                'phone' => '+62834567890',
                'email' => 'david.chen@email.com',
                'check_in' => Carbon::now()->subDays(3),
                'check_out' => Carbon::now()->addDays(1),
            ],
            [
                'name' => 'Sarah Johnson',
                'phone' => '+62845678901',
                'email' => 'sarah.johnson@email.com',
                'check_in' => Carbon::now(),
                'check_out' => Carbon::now()->addDays(5),
            ],
            [
                'name' => 'Robert Kim',
                'phone' => '+62856789012',
                'email' => 'robert.kim@email.com',
                'check_in' => Carbon::tomorrow(),
                'check_out' => Carbon::now()->addDays(7),
            ],
            [
                'name' => 'Emma Wilson',
                'phone' => '+62867890123',
                'email' => 'emma.wilson@email.com',
                'check_in' => Carbon::now()->addDays(2),
                'check_out' => Carbon::now()->addDays(6),
            ],
            [
                'name' => 'Hassan Ahmed',
                'phone' => '+62878901234',
                'email' => 'hassan.ahmed@email.com',
                'check_in' => Carbon::now()->subDays(5),
                'check_out' => Carbon::tomorrow(),
            ],
            [
                'name' => 'Sophie Martin',
                'phone' => '+62889012345',
                'email' => 'sophie.martin@email.com',
                'check_in' => Carbon::now()->subDays(4),
                'check_out' => Carbon::now()->addDays(2),
            ],
            [
                'name' => 'Daniel Lee',
                'phone' => '+62890123456',
                'email' => 'daniel.lee@email.com',
                'check_in' => Carbon::now()->subDays(1),
                'check_out' => Carbon::now()->addDays(3),
            ],
            [
                'name' => 'Olivia Brown',
                'phone' => '+62801234567',
                'email' => 'olivia.brown@email.com',
                'check_in' => Carbon::now()->addDays(1),
                'check_out' => Carbon::now()->addDays(8),
            ],
        ];

        // Create guests and assign rooms
        $guests = [];

        // Make sure we don't run out of rooms
        $numRooms = count($roomIds);
        $numGuests = count($guestDetails);

        if ($numRooms < $numGuests) {
            $this->command->info("Warning: Not enough rooms ({$numRooms}) for all guests ({$numGuests}). Some guests will share rooms.");
        }

        foreach ($guestDetails as $index => $guestDetail) {
            // Assign a room from available rooms
            $roomId = $roomIds[$index % $numRooms];

            // Add room_id to guest details
            $guestDetail['room_id'] = $roomId;

            // Add to guests array
            $guests[] = $guestDetail;
        }

        foreach ($guests as $guest) {
            Guest::create($guest);
        }
    }
}
