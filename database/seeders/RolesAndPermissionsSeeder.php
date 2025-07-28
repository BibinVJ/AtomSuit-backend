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
        // $superAdminRole->syncPermissions(Permission::all());


        // ADMIN - Assign all permissions
        $adminRole = Role::firstOrCreate(['name' => RolesEnum::ADMIN->value, 'guard_name' => $guard]);
        $adminRole->syncPermissions(Permission::all());


        // INVENTORY MANAGER
        $InventoryManagerRole = Role::firstOrCreate(['name' => RolesEnum::INVENTORY_MANAGER->value, 'guard_name' => $guard]);
        $InventoryManagerRole->syncPermissions([
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

            // Purchase
            PermissionsEnum::VIEW_PURCHASE->value,
            PermissionsEnum::CREATE_PURCHASE->value,
            PermissionsEnum::UPDATE_PURCHASE->value,
            PermissionsEnum::DELETE_PURCHASE->value,
        ]);



        // SALES PERSON
        $salesPersonRole = Role::firstOrCreate(['name' => RolesEnum::SALES_PERSON->value, 'guard_name' => $guard]);
        $salesPersonRole->syncPermissions([
            PermissionsEnum::VIEW_DASHBOARD->value,

            // Customer
            PermissionsEnum::VIEW_CUSTOMER->value,
            PermissionsEnum::CREATE_CUSTOMER->value,
            PermissionsEnum::UPDATE_CUSTOMER->value,
            PermissionsEnum::DELETE_CUSTOMER->value,

            // Sale
            PermissionsEnum::VIEW_SALE->value,
            PermissionsEnum::CREATE_SALE->value,
            PermissionsEnum::UPDATE_SALE->value,
            PermissionsEnum::DELETE_SALE->value,
        ]);
    }
}
