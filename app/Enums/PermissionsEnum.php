<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // Dashboard
    case VIEW_DASHBOARD = 'view-dashboard';
    case MANAGE_NOTIFICATIONS = 'manage-notifications';

    // Plan Management
    case CREATE_PLAN = 'create-plan';
    case UPDATE_PLAN = 'update-plan';
    case DELETE_PLAN = 'delete-plan';

    // Tenant Management
    case VIEW_TENANT = 'view-tenant';
    case CREATE_TENANT = 'create-tenant';
    case UPDATE_TENANT = 'update-tenant';
    case DELETE_TENANT = 'delete-tenant';

    // Subscription Management
    case VIEW_SUBSCRIPTION = 'view-subscription';
    case MY_SUBSCRIPTION = 'my-subscription';

    // Domain Management
    case VIEW_DOMAIN = 'view-domain';

    // User Management
    case VIEW_USER = 'view-user';
    case CREATE_USER = 'create-user';
    case UPDATE_USER = 'update-user';
    case DELETE_USER = 'delete-user';
    case VIEW_USER_LOGIN_DETAILS = 'view-user-login-details';

    // Role & Permission Management
    case VIEW_ROLE = 'view-role';
    case CREATE_ROLE = 'create-role';
    case UPDATE_ROLE = 'update-role';
    case DELETE_ROLE = 'delete-role';
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

    // sale management
    case VIEW_SALE = 'view-sale';
    case CREATE_SALE = 'create-sale';
    case UPDATE_SALE = 'update-sale';
    case DELETE_SALE = 'delete-sale';

    // currency management
    case VIEW_CURRENCY = 'view-currency';
    case CREATE_CURRENCY = 'create-currency';
    case UPDATE_CURRENCY = 'update-currency';
    case DELETE_CURRENCY = 'delete-currency';

    // Account Groups
    case VIEW_ACCOUNT_GROUP = 'view-account-group';
    case CREATE_ACCOUNT_GROUP = 'create-account-group';
    case UPDATE_ACCOUNT_GROUP = 'update-account-group';
    case DELETE_ACCOUNT_GROUP = 'delete-account-group';

    // Chart of Accounts
    case VIEW_CHART_OF_ACCOUNT = 'view-chart-of-account';
    case CREATE_CHART_OF_ACCOUNT = 'create-chart-of-account';
    case UPDATE_CHART_OF_ACCOUNT = 'update-chart-of-account';
    case DELETE_CHART_OF_ACCOUNT = 'delete-chart-of-account';

    // exchange rate management
    case VIEW_EXCHANGE_RATE = 'view-exchange-rate';
    case CREATE_EXCHANGE_RATE = 'create-exchange-rate';
    case UPDATE_EXCHANGE_RATE = 'update-exchange-rate';
    case DELETE_EXCHANGE_RATE = 'delete-exchange-rate';

    // warehouse management
    case VIEW_WAREHOUSE = 'view-warehouse';
    case CREATE_WAREHOUSE = 'create-warehouse';
    case UPDATE_WAREHOUSE = 'update-warehouse';
    case DELETE_WAREHOUSE = 'delete-warehouse';

    // Tax Management
    case VIEW_TAX_RATE = 'view-tax-rate';
    case CREATE_TAX_RATE = 'create-tax-rate';
    case UPDATE_TAX_RATE = 'update-tax-rate';
    case DELETE_TAX_RATE = 'delete-tax-rate';

    case VIEW_TAX_GROUP = 'view-tax-group';
    case CREATE_TAX_GROUP = 'create-tax-group';
    case UPDATE_TAX_GROUP = 'update-tax-group';
    case DELETE_TAX_GROUP = 'delete-tax-group';

    // Cost Center
    case VIEW_COST_CENTER = 'view-cost-center';
    case CREATE_COST_CENTER = 'create-cost-center';
    case UPDATE_COST_CENTER = 'update-cost-center';
    case DELETE_COST_CENTER = 'delete-cost-center';

    // Audit
    case VIEW_AUDIT = 'view-audit';

    // Price List Management
    case VIEW_PRICE_LIST = 'view-price-list';
    case CREATE_PRICE_LIST = 'create-price-list';
    case UPDATE_PRICE_LIST = 'update-price-list';
    case DELETE_PRICE_LIST = 'delete-price-list';

    // Item Price Management
    case VIEW_ITEM_PRICE = 'view-item-price';
    case CREATE_ITEM_PRICE = 'create-item-price';
    case UPDATE_ITEM_PRICE = 'update-item-price';
    case DELETE_ITEM_PRICE = 'delete-item-price';

    // System Monitoring / Analytics
    case VIEW_SYSTEM_ANALYTICS = 'view-system-analytics';

    // Module Management
    case VIEW_MODULE = 'view-module';
    case MANAGE_MODULE = 'manage-module';

    // Purchase Order
    case VIEW_PURCHASE_ORDER = 'view-purchase-order';
    case CREATE_PURCHASE_ORDER = 'create-purchase-order';
    case UPDATE_PURCHASE_ORDER = 'update-purchase-order';
    case DELETE_PURCHASE_ORDER = 'delete-purchase-order';

    // Goods Received Note
    case VIEW_GRN = 'view-grn';
    case CREATE_GRN = 'create-grn';
    case UPDATE_GRN = 'update-grn';
    case DELETE_GRN = 'delete-grn';

    // Purchase Invoice
    case VIEW_PURCHASE_INVOICE = 'view-purchase-invoice';
    case CREATE_PURCHASE_INVOICE = 'create-purchase-invoice';
    case UPDATE_PURCHASE_INVOICE = 'update-purchase-invoice';
    case DELETE_PURCHASE_INVOICE = 'delete-purchase-invoice';

    // Debit Note
    case VIEW_DEBIT_NOTE = 'view-debit-note';
    case CREATE_DEBIT_NOTE = 'create-debit-note';
    case UPDATE_DEBIT_NOTE = 'update-debit-note';
    case DELETE_DEBIT_NOTE = 'delete-debit-note';

    // Vendor Payment
    case VIEW_VENDOR_PAYMENT = 'view-vendor-payment';
    case CREATE_VENDOR_PAYMENT = 'create-vendor-payment';
    case UPDATE_VENDOR_PAYMENT = 'update-vendor-payment';
    case DELETE_VENDOR_PAYMENT = 'delete-vendor-payment';

    public static function centralPermissions(): array
    {
        return [
            // Dashboard
            self::VIEW_DASHBOARD->value,
            self::MANAGE_NOTIFICATIONS->value,
            self::VIEW_AUDIT->value,

            // System Monitoring
            self::VIEW_SYSTEM_ANALYTICS->value,

            // Module Management
            self::VIEW_MODULE->value,
            self::MANAGE_MODULE->value,

            // Plan
            self::CREATE_PLAN->value,
            self::UPDATE_PLAN->value,
            self::DELETE_PLAN->value,

            // Tenant
            self::VIEW_TENANT->value,
            self::CREATE_TENANT->value,
            self::UPDATE_TENANT->value,
            self::DELETE_TENANT->value,

            // Subscription
            self::VIEW_SUBSCRIPTION->value,

            // Domain
            self::VIEW_DOMAIN->value,

            // User
            self::VIEW_USER->value,
            self::CREATE_USER->value,
            self::UPDATE_USER->value,
            self::DELETE_USER->value,
            self::VIEW_USER_LOGIN_DETAILS->value,

            // Role & Permission
            self::VIEW_ROLE->value,
            self::CREATE_ROLE->value,
            self::UPDATE_ROLE->value,
            self::DELETE_ROLE->value,
            self::VIEW_PERMISSION->value,

            // System / Settings
            self::VIEW_SETTING->value,
            self::UPDATE_SETTING->value,
            self::VIEW_COMPANY_INFO->value,
            self::UPDATE_COMPANY_INFO->value,
        ];
    }

    /**
     * Returns a list of permissions that are available for tenants
     * we can exclude central permissions from this list
     */
    public static function tenantPermissions(): array
    {
        // exclude central permissions
        $excluded = [
            self::CREATE_PLAN->value,
            self::UPDATE_PLAN->value,
            self::DELETE_PLAN->value,

            self::VIEW_TENANT->value,
            self::CREATE_TENANT->value,
            self::UPDATE_TENANT->value,
            self::DELETE_TENANT->value,

            self::VIEW_DOMAIN->value,

            self::VIEW_SUBSCRIPTION->value,

            self::VIEW_SYSTEM_ANALYTICS->value,
        ];

        // load all the permission except excluded
        return collect(self::cases())
            ->map(fn ($case) => $case->value)
            ->reject(fn ($permission) => in_array($permission, $excluded, true))
            ->values()
            ->toArray();
    }
}
