<?php

namespace Database\Seeders;

use App\Models\CentralUser;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CentralDatabaseSeeder extends Seeder
{
    /**
     * Run the central database seeds.
     */
    public function run(): void
    {
        // Create central permissions and roles
        $this->createRolesAndPermissions();

        // Create super admin user
        $this->createSuperAdmin();

        // Create default tenant
        $this->createDefaultTenant();
    }

    private function createRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for central user
        $permissions = [
            'manage tenants',
            'create tenants',
            'view tenants',
            'edit tenants',
            'delete tenants',
            'manage central users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'central'],
                ['name' => $permission, 'guard_name' => 'central']
            );
        }

        // Create super admin role
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'central'],
            ['name' => 'super-admin', 'guard_name' => 'central']
        );

        // Assign all permissions to super admin
        $superAdminRole->syncPermissions($permissions);
    }

    private function createSuperAdmin(): void
    {
        $superAdmin = CentralUser::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $superAdmin->assignRole('super-admin');

        $this->command->info('Super Admin created: admin@admin.com / password');
    }

    private function createDefaultTenant(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['id' => 'default'],
            [
                'id' => 'default',
                'data' => [
                    'name' => 'Default Tenant',
                    'email' => 'tenant@example.com',
                    'plan' => 'basic',
                ],
            ]
        );

        // Create domain for default tenant
        $tenant->domains()->firstOrCreate(
            ['domain' => 'default.localhost'],
            ['domain' => 'default.localhost']
        );

        $this->command->info('Default tenant created: default.localhost');
    }
}