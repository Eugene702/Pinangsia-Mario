<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create cleaning staff
        $cleaningStaff = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'housekeeping',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'housekeeping',
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'housekeeping',
            ],
            [
                'name' => 'Dewi Permata',
                'email' => 'dewi@hotel.com',
                'password' => Hash::make('password'),
                'role' => 'housekeeping',
            ],
        ];

        foreach ($cleaningStaff as $staff) {
            User::create($staff);
        }
    }
}
