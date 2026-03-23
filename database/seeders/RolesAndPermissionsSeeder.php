<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'api');

        /**
         * Create all permissions
         *
         * Permissions are gathered from the permission enum,
         * to add/create a new permission add it to the enum list.
         */
        foreach (PermissionsEnum::tenantPermissions() as $permissionEnum) {
            Permission::updateOrCreate([
                'name' => $permissionEnum,
                'guard_name' => $guard,
            ]);
        }

        /**
         * Create and assign permissions to roles
         */
        // ADMIN - Assign all permissions
        $adminRole = Role::updateOrCreate(['name' => RolesEnum::ADMIN->value, 'guard_name' => $guard]);
        $adminRole->syncPermissions(Permission::all());

        // INVENTORY MANAGER
        $InventoryManagerRole = Role::updateOrCreate(['name' => RolesEnum::INVENTORY_MANAGER->value, 'guard_name' => $guard]);
        $InventoryManagerRole->syncPermissions([
            // Dashboard
            PermissionsEnum::VIEW_DASHBOARD->value,

            // Category
            PermissionsEnum::VIEW_CATEGORY->value,
            PermissionsEnum::CREATE_CATEGORY->value,
            PermissionsEnum::UPDATE_CATEGORY->value,
            PermissionsEnum::DELETE_CATEGORY->value,

            // Unit
            PermissionsEnum::VIEW_UNIT->value,
            PermissionsEnum::CREATE_UNIT->value,
            PermissionsEnum::UPDATE_UNIT->value,
            PermissionsEnum::DELETE_UNIT->value,

            // Item
            PermissionsEnum::VIEW_ITEM->value,
            PermissionsEnum::CREATE_ITEM->value,
            PermissionsEnum::UPDATE_ITEM->value,
            PermissionsEnum::DELETE_ITEM->value,

            // Batch
            PermissionsEnum::VIEW_BATCH->value,

            // Vendor
            PermissionsEnum::VIEW_VENDOR->value,
            PermissionsEnum::CREATE_VENDOR->value,
            PermissionsEnum::UPDATE_VENDOR->value,
            PermissionsEnum::DELETE_VENDOR->value,

            // Cost Center
            PermissionsEnum::VIEW_COST_CENTER->value,
            PermissionsEnum::CREATE_COST_CENTER->value,
            PermissionsEnum::UPDATE_COST_CENTER->value,
            PermissionsEnum::DELETE_COST_CENTER->value,

            // Purchase
            PermissionsEnum::VIEW_PURCHASE_ORDER->value,
            PermissionsEnum::CREATE_PURCHASE_ORDER->value,
            PermissionsEnum::UPDATE_PURCHASE_ORDER->value,
            PermissionsEnum::DELETE_PURCHASE_ORDER->value,
        ]);

        // SALES PERSON
        $salesPersonRole = Role::updateOrCreate(['name' => RolesEnum::SALES_PERSON->value, 'guard_name' => $guard]);
        $salesPersonRole->syncPermissions([
            // Dashboard
            PermissionsEnum::VIEW_DASHBOARD->value,

            // Customer
            PermissionsEnum::VIEW_CUSTOMER->value,
            PermissionsEnum::CREATE_CUSTOMER->value,
            PermissionsEnum::UPDATE_CUSTOMER->value,
            PermissionsEnum::DELETE_CUSTOMER->value,

            // Item
            PermissionsEnum::VIEW_ITEM->value,
        ]);
    }
}
