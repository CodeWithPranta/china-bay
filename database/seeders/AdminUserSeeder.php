<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@chinabaybd.com'],
            [
                'name' => 'ChinaBay Admin',
                'phone' => '+8801849415277',
                'password' => Hash::make('password'),
                'locale' => 'bn',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');
    }
}
