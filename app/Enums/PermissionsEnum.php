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
    case VIEW_COMPANY_INFO = 'view-company-info';
    case UPDATE_COMPANY_INFO = 'update-company-info';

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

}
