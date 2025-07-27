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
            static::SUPER_ADMIN => 'Super Admin',
            static::ADMIN => 'Admin',
            static::INVENTORY_MANAGER => 'Inventory Manager',
            static::SALES_PERSON => 'Sales Person',
            default => 'Unknown Role',
        };
    }
}
