<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sponsor = User::firstOrCreate(
            ['email' => 'sponsor@example.com'],
            [
                'name' => 'Sponsor User',
                'password' => Hash::make('Cubet@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
                'status_updated_at' => now(),
            ]
        );
        $sponsor->assignRole(RolesEnum::SPONSOR->value);

        $exhibitor = User::firstOrCreate(
            ['email' => 'exhibitor@example.com'],
            [
                'name' => 'Exhibitor User',
                'password' => Hash::make('Cubet@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
                'status_updated_at' => now(),
            ]
        );
        $exhibitor->assignRole(RolesEnum::EXHIBITOR->value);
    }
}
