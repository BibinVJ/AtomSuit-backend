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
        $inventoryManager = User::firstOrCreate(
            ['email' => 'inventory.manager@example.com'],
            [
                'name' => 'Inventory Manager User',
                'password' => Hash::make('Example@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
                'status_updated_at' => now(),
            ]
        );
        $inventoryManager->assignRole(RolesEnum::INVENTORY_MANAGER->value);

        $salesPerson = User::firstOrCreate(
            ['email' => 'salesperson@example.com'],
            [
                'name' => 'Sales Person User',
                'password' => Hash::make('Example@123'),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
                'status_updated_at' => now(),
            ]
        );
        $salesPerson->assignRole(RolesEnum::SALES_PERSON->value);
    }
}
