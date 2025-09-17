<?php

namespace Database\Seeders;

use App\Enums\RolesEnum;
use App\Models\Category;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantDefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant database.
     */
    public function run(): void
    {
        // Create tenant permissions and roles
        $this->createRolesAndPermissions();

        // Create default admin user for tenant
        $this->createTenantAdmin();

        // Create default categories and units
        $this->createDefaultCategories();
        $this->createDefaultUnits();
    }

    private function createRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for tenant users
        $permissions = [
            'manage users',
            'view dashboard',
            'manage inventory',
            'manage sales',
            'manage purchases',
            'manage customers',
            'manage vendors',
            'view reports',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => RolesEnum::ADMIN->value, 'guard_name' => 'api'],
            ['name' => RolesEnum::ADMIN->value, 'guard_name' => 'api']
        );

        $userRole = Role::firstOrCreate(
            ['name' => RolesEnum::USER->value, 'guard_name' => 'api'],
            ['name' => RolesEnum::USER->value, 'guard_name' => 'api']
        );

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions);
        $userRole->syncPermissions(['view dashboard', 'manage inventory', 'manage sales']);
    }

    private function createTenantAdmin(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@' . tenant('id') . '.com'],
            [
                'name' => 'Tenant Admin',
                'email' => 'admin@' . tenant('id') . '.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole(RolesEnum::ADMIN->value);

        $this->command->info('Tenant Admin created: admin@' . tenant('id') . '.com / password');
    }

    private function createDefaultCategories(): void
    {
        $categories = [
            ['name' => 'General', 'description' => 'General category', 'is_active' => true],
            ['name' => 'Electronics', 'description' => 'Electronic items', 'is_active' => true],
            ['name' => 'Clothing', 'description' => 'Clothing items', 'is_active' => true],
            ['name' => 'Food & Beverages', 'description' => 'Food and beverage items', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        $this->command->info('Default categories created');
    }

    private function createDefaultUnits(): void
    {
        $units = [
            ['name' => 'Piece', 'code' => 'PCS', 'description' => 'Individual pieces', 'is_active' => true],
            ['name' => 'Kilogram', 'code' => 'KG', 'description' => 'Weight in kilograms', 'is_active' => true],
            ['name' => 'Liter', 'code' => 'L', 'description' => 'Volume in liters', 'is_active' => true],
            ['name' => 'Meter', 'code' => 'M', 'description' => 'Length in meters', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['code' => $unit['code']],
                $unit
            );
        }

        $this->command->info('Default units created');
    }
}
