<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('Example@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::Active,
                'status_updated_at' => now(),
            ]
        );

        $admin->assignRole(RolesEnum::ADMIN->value);
    }
}
