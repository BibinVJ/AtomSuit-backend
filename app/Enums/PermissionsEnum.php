<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // note: case NAME_IN_APP = 'name-in-database';

    // Dashboard
    case VIEW_DASHBOARD = 'view-dashboard';

    case MANAGE_NOTIFICATIONS = 'manage-notifications';

    // User Management
    case VIEW_USER = 'view-user';
    case CREATE_USER = 'create-user';
    case UPDATE_USER = 'update-user';
    case DELETE_USER = 'delete-user';

    // Role Management
    case VIEW_ROLE = 'view-role';
    case CREATE_ROLE = 'create-role';
    case UPDATE_ROLE = 'update-role';
    case DELETE_ROLE = 'delete-role';

    // Permission Management
    case VIEW_PERMISSION = 'view-permission';

    // Settings
    case VIEW_SETTING = 'view-setting';
    case UPDATE_SETTING = 'update-setting';


    // category management
    case VIEW_CATEGORY = 'view-category';
    case CREATE_CATEGORY = 'create-category';
    case UPDATE_CATEGORY = 'update-category';
    case DELETE_CATEGORY = 'delete-category';

    // unit management
    case VIEW_UNIT = 'view-unit';
    case CREATE_UNIT = 'create-unit';
    case UPDATE_UNIT = 'update-unit';
    case DELETE_UNIT = 'delete-unit';

    // item management
    case VIEW_ITEM = 'view-item';
    case CREATE_ITEM = 'create-item';
    case UPDATE_ITEM = 'update-item';
    case DELETE_ITEM = 'delete-item';

    // batch management
    case VIEW_BATCH = 'view-batch';

    // vendor management
    case VIEW_VENDOR = 'view-vendor';
    case CREATE_VENDOR = 'create-vendor';
    case UPDATE_VENDOR = 'update-vendor';
    case DELETE_VENDOR = 'delete-vendor';

    // customer management
    case VIEW_CUSTOMER = 'view-customer';
    case CREATE_CUSTOMER = 'create-customer';
    case UPDATE_CUSTOMER = 'update-customer';
    case DELETE_CUSTOMER = 'delete-customer';

    // purchase management
    case VIEW_PURCHASE = 'view-purchase';
    case CREATE_PURCHASE = 'create-purchase';
    case UPDATE_PURCHASE = 'update-purchase';
    case DELETE_PURCHASE = 'delete-purchase';

    // sale management
    case VIEW_SALE = 'view-sale';
    case CREATE_SALE = 'create-sale';
    case UPDATE_SALE = 'update-sale';
    case DELETE_SALE = 'delete-sale';



    public function label(): string
    {
        return match ($this) {
            self::VIEW_DASHBOARD     => 'View Dashboard',

            self::VIEW_USER          => 'View Users',
            self::CREATE_USER        => 'Create User',
            self::UPDATE_USER        => 'Update User',
            self::DELETE_USER        => 'Delete User',

            self::VIEW_ROLE          => 'View Roles',
            self::CREATE_ROLE        => 'Create Role',
            self::UPDATE_ROLE        => 'Update Role',
            self::DELETE_ROLE        => 'Delete Role',

            self::VIEW_PERMISSION    => 'View Permissions',

            self::VIEW_SETTING       => 'View Settings',
            self::UPDATE_SETTING     => 'Update Settings',

            self::VIEW_CATEGORY      => 'View Categories',
            self::CREATE_CATEGORY    => 'Create Category',
            self::UPDATE_CATEGORY    => 'Update Category',
            self::DELETE_CATEGORY    => 'Delete Category',

            self::VIEW_UNIT          => 'View Units',
            self::CREATE_UNIT        => 'Create Unit',
            self::UPDATE_UNIT        => 'Update Unit',
            self::DELETE_UNIT        => 'Delete Unit',

            self::VIEW_ITEM          => 'View Items',
            self::CREATE_ITEM        => 'Create Item',
            self::UPDATE_ITEM        => 'Update Item',
            self::DELETE_ITEM        => 'Delete Item',

            self::VIEW_BATCH         => 'View Batches',

            self::VIEW_VENDOR        => 'View Vendors',
            self::CREATE_VENDOR      => 'Create Vendor',
            self::UPDATE_VENDOR      => 'Update Vendor',
            self::DELETE_VENDOR      => 'Delete Vendor',

            self::VIEW_CUSTOMER      => 'View Customers',
            self::CREATE_CUSTOMER    => 'Create Customer',
            self::UPDATE_CUSTOMER    => 'Update Customer',
            self::DELETE_CUSTOMER    => 'Delete Customer',

            self::VIEW_PURCHASE      => 'View Purchases',
            self::CREATE_PURCHASE    => 'Create Purchase',
            self::UPDATE_PURCHASE    => 'Update Purchase',
            self::DELETE_PURCHASE    => 'Delete Purchase',

            self::VIEW_SALE          => 'View Sales',
            self::CREATE_SALE        => 'Create Sale',
            self::UPDATE_SALE        => 'Update Sale',
            self::DELETE_SALE        => 'Delete Sale',

            default                  => 'Unknown',
        };
    }
}
