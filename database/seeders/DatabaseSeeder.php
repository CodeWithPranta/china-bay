<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // NB: never use factory()->make(...)->toArray() here — the User
        // model hides `password`, so toArray() drops it and MySQL
        // strict mode rejects the insert.
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            RolesPermissionsSeeder::class,
            AdminUserSeeder::class,
            LocaleSeeder::class,
            SettingsSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
