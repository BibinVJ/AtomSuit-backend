<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CentralRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.guard_names.central');

        /**
         * Create all permissions
         *
         * Permissions are gathered from the permission enum,
         * to add/create a new permission add it to the enum list.
         */
        foreach (PermissionsEnum::centralPermissions() as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        /**
         * Create and assign permissions to roles
         */
        // SUPER ADMIN - Assign all permissions
        $superAdminRole = Role::firstOrCreate(['name' => RolesEnum::SUPER_ADMIN->value, 'guard_name' => $guard]);
        $superAdminRole->syncPermissions(Permission::all());

    }
}
