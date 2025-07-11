<?php

namespace Database\Seeders;

use App\Models\Request;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RequestsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get guests
        $guests = Guest::all();
        if ($guests->isEmpty()) {
            $this->command->info('No guests found. Please run GuestsTableSeeder first.');
            return;
        }

        // Get maintenance staff IDs
        $maintenanceStaff = User::where('role', 'maintenance')->pluck('id')->toArray();
        if (empty($maintenanceStaff)) {
            $this->command->info('No maintenance staff found. Please run UsersTableSeeder first.');
            return;
        }

        // Request types
        $requestTypes = [
            'towel_replacement',
            'room_cleaning',
            'maintenance',
            'extra_amenities',
            'laundry_service',
            'food_service',
            'wifi_issue',
            'plumbing_issue',
            'ac_issue',
            'tv_issue',
        ];

        // Descriptions for each request type
        $descriptions = [
            'towel_replacement' => [
                'Need fresh towels please',
                'Requesting clean towels for bathroom',
                'Please replace used towels',
            ],
            'room_cleaning' => [
                'Please clean my room',
                'Need room service when convenient',
                'Room needs cleaning today',
            ],
            'maintenance' => [
                'Light bulb needs replacing',
                'TV remote not working',
                'Window lock is broken',
                'Leaking faucet in bathroom',
            ],
            'extra_amenities' => [
                'Need extra pillows',
                'Request for toothpaste and toothbrush',
                'Can I get an extra blanket?',
                'Need more coffee pods',
            ],
            'laundry_service' => [
                'Need clothes washed urgently',
                'Requesting laundry service for tomorrow',
                'Have clothes that need dry cleaning',
            ],
            'food_service' => [
                'Room service order',
                'Breakfast request for tomorrow morning',
                'Want to order dinner to room',
            ],
            'wifi_issue' => [
                'Wi-Fi connection is very slow',
                'Cannot connect to hotel Wi-Fi',
                'Internet keeps disconnecting',
            ],
            'plumbing_issue' => [
                'Toilet is clogged',
                'Shower drain is not working properly',
                'Sink is leaking water',
            ],
            'ac_issue' => [
                'AC is not cooling properly',
                'Air conditioner makes strange noise',
                'Need help adjusting room temperature',
            ],
            'tv_issue' => [
                'TV not turning on',
                'Remote control not working',
                'No signal on certain channels',
            ],
        ];

        // Create various requests
        $requests = [];

        // Completed requests
        foreach ($guests as $guest) {
            $numRequests = rand(0, 3);
            for ($i = 0; $i < $numRequests; $i++) {
                $type = $requestTypes[array_rand($requestTypes)];
                $descriptionArray = $descriptions[$type];
                $description = $descriptionArray[array_rand($descriptionArray)];

                // For maintenance type requests, assign maintenance staff
                $assignedTo = null;
                if (
                    $type === 'maintenance' || $type === 'wifi_issue' || $type === 'plumbing_issue' ||
                    $type === 'ac_issue' || $type === 'tv_issue'
                ) {
                    $assignedTo = $maintenanceStaff[array_rand($maintenanceStaff)];
                }

                // Check if guest has a valid room_id
                if ($guest->room_id) {
                    $requests[] = [
                        'room_id' => $guest->room_id,
                        'guest_id' => $guest->id,
                        'type' => $type,
                        'description' => $description,
                        'status' => 'completed',
                        'assigned_to' => $assignedTo,
                        'completed_at' => Carbon::now()->subHours(rand(1, 48)),
                    ];
                }
            }
        }

        // Pending and in progress requests
        foreach ($guests->random(5) as $guest) {
            $type = $requestTypes[array_rand($requestTypes)];
            $descriptionArray = $descriptions[$type];
            $description = $descriptionArray[array_rand($descriptionArray)];

            // For maintenance type requests, assign maintenance staff
            $assignedTo = null;
            if (
                $type === 'maintenance' || $type === 'wifi_issue' || $type === 'plumbing_issue' ||
                $type === 'ac_issue' || $type === 'tv_issue'
            ) {
                $assignedTo = $maintenanceStaff[array_rand($maintenanceStaff)];
            }

            // Check if guest has a valid room_id
            if ($guest->room_id) {
                $requests[] = [
                    'room_id' => $guest->room_id,
                    'guest_id' => $guest->id,
                    'type' => $type,
                    'description' => $description,
                    'status' => 'pending',
                    'assigned_to' => $assignedTo,
                    'completed_at' => null,
                ];
            }
        }

        foreach ($guests->random(3) as $guest) {
            $type = $requestTypes[array_rand($requestTypes)];
            $descriptionArray = $descriptions[$type];
            $description = $descriptionArray[array_rand($descriptionArray)];

            // For maintenance type requests, assign maintenance staff
            $assignedTo = null;
            if (
                $type === 'maintenance' || $type === 'wifi_issue' || $type === 'plumbing_issue' ||
                $type === 'ac_issue' || $type === 'tv_issue'
            ) {
                $assignedTo = $maintenanceStaff[array_rand($maintenanceStaff)];
            }

            // Check if guest has a valid room_id
            if ($guest->room_id) {
                $requests[] = [
                    'room_id' => $guest->room_id,
                    'guest_id' => $guest->id,
                    'type' => $type,
                    'description' => $description,
                    'status' => 'in_progress',
                    'assigned_to' => $assignedTo,
                    'completed_at' => null,
                ];
            }
        }

        // Insert all requests
        foreach ($requests as $request) {
            Request::create($request);
        }
    }
}
