<?php

namespace App\Enums;

enum RolesEnum: string
{
    // note: case NAME_IN_APP = 'name-in-database';

    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
    case INVENTORY_MANAGER = 'inventory-manager';
    case SALES_PERSON = 'sales-person';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::INVENTORY_MANAGER => 'Inventory Manager',
            self::SALES_PERSON => 'Sales Person',
        };
    }
}
