<?php

namespace App\Enums;

enum RolesEnum: string
{
    // note: case NAME_IN_APP = 'name-in-database';

    case ADMIN = 'admin';
    case SPONSOR = 'sponsor';
    case EXHIBITOR = 'exhibitor';
    case GUEST     = 'guest';

    public function label(): string
    {
        return match ($this) {
            static::ADMIN => 'Admin',
            static::SPONSOR => 'Sponsor',
            static::EXHIBITOR => 'Exhibitor',
            self::GUEST     => 'Guest',
        };
    }
}
