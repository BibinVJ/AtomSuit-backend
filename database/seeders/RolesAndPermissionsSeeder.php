<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\RolesEnum;
use App\Enums\PermissionsEnum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('permission.defaults.guard');

        /**
         * Create all permissions
         * 
         * Permissions are gathered from the permission enum,
         * to add/create a new permission add it to the enum list.
         */ 
        foreach (PermissionsEnum::cases() as $permissionEnum) {
            Permission::firstOrCreate([
                'name' => $permissionEnum->value,
                'guard_name' => $guard
            ]);
        }


        /**
         * Create and assign permissions to roles
         */
        // SUPER ADMIN - Assign all permissions
        $superAdminRole = Role::firstOrCreate(['name' => RolesEnum::SUPER_ADMIN->value, 'guard_name' => $guard]);
        $superAdminRole->syncPermissions(Permission::all());

        
        // ADMIN - Assign all permissions
        $adminRole = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'guard_name' => $guard]);
        $adminRole->syncPermissions(Permission::all());

        
        // INVENTORY MANAGER
        $sponsorRole = Role::firstOrCreate(['name' => RolesEnum::INVENTORY_MANAGER->value, 'guard_name' => $guard]);
        $sponsorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,
        ]);


        // SALES PERSON
        $exhibitorRole = Role::firstOrCreate(['name' => RolesEnum::SALES_PERSON->value, 'guard_name' => $guard]);
        $exhibitorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,

        ]);
    }
}
