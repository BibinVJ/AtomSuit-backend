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
        $adminRole = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value]);
        $adminRole->syncPermissions(Permission::all());

        // SPONSOR
        $sponsorRole = Role::firstOrCreate(['name' => RolesEnum::SPONSOR->value]);
        $sponsorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,

            PermissionsEnum::VIEW_BOOTH->value,
            PermissionsEnum::BOOK_BOOTH->value,

            PermissionsEnum::VIEW_BOOKING->value,
            PermissionsEnum::CREATE_BOOKING->value,
            PermissionsEnum::UPDATE_BOOKING->value,
            PermissionsEnum::DELETE_BOOKING->value,
        ]);

        // EXHIBITOR
        $exhibitorRole = Role::firstOrCreate(['name' => RolesEnum::EXHIBITOR->value]);
        $exhibitorRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,

            PermissionsEnum::VIEW_BOOTH->value,
            PermissionsEnum::BOOK_BOOTH->value,

            PermissionsEnum::VIEW_BOOKING->value,
            PermissionsEnum::CREATE_BOOKING->value,
            PermissionsEnum::UPDATE_BOOKING->value,
            PermissionsEnum::DELETE_BOOKING->value,
        ]);
    }
}
