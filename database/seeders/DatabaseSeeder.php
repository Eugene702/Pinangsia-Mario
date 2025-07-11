<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Inventory;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users with different roles
        User::create([
            'name' => 'Housekeeping Manager',
            'email' => 'manager@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        User::create([
            'name' => 'Receptionist',
            'email' => 'receptionist@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'receptionist',
        ]);

        User::create([
            'name' => 'Housekeeping Staff',
            'email' => 'staff@hotel.com',
            'password' => bcrypt('password'),
            'role' => 'housekeeping',
        ]);

        // Create rooms
        for ($i = 101; $i <= 110; $i++) {
            Room::create([
                'room_number' => $i,
                'status' => 'available',
            ]);
        }

        // Create basic inventory items
        $items = [
            ['name' => 'Handuk', 'quantity' => 100, 'minimum_stock' => 30],
            ['name' => 'Sabun', 'quantity' => 200, 'minimum_stock' => 50],
            ['name' => 'Shampo', 'quantity' => 200, 'minimum_stock' => 50],
            ['name' => 'Seprai', 'quantity' => 50, 'minimum_stock' => 20],
            ['name' => 'Sarung Bantal', 'quantity' => 80, 'minimum_stock' => 30],
        ];

        foreach ($items as $item) {
            Inventory::create($item);
        }

        $this->call([
            UsersTableSeeder::class,
            GuestsTableSeeder::class,
            CleaningSchedulesTableSeeder::class,
            RequestsTableSeeder::class
        ]);
    }
}
