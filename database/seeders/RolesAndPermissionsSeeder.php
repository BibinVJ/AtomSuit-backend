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

        
        /**
         * Create all permissions
         * 
         * Permissions are gathered from the permission enum,
         * to add/create a new permission add it to the enum list.
         */ 
        foreach (PermissionsEnum::cases() as $permissionEnum) {
            Permission::firstOrCreate([
                'name' => $permissionEnum->value,
                'guard_name' => config('permission.defaults.guard')
            ]);
        }


        /**
         * Create and assign permissions to roles
         */
        // ADMIN - Assign all permissions
        $adminRole = Role::firstOrCreate(['name' => RolesEnum::SUPER_ADMIN->value]);
        $adminRole->syncPermissions(Permission::all());

        $adminRole = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value]);
        $adminRole->syncPermissions(Permission::all());

        
        // INVENTORY MANAGER
        $sponsorRole = Role::firstOrCreate(['name' => RolesEnum::INVENTORY_MANAGER->value]);
        $sponsorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,
        ]);


        // SALES PERSON
        $exhibitorRole = Role::firstOrCreate(['name' => RolesEnum::SALES_PERSON->value]);
        $exhibitorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,

        ]);
    }
}
