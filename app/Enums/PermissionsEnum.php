<?php

namespace App\Enums;

enum PermissionsEnum: string
{
    // note: case NAME_IN_APP = 'name-in-database';

    // Dashboard
    case VIEW_DASHBOARD = 'view-dashboard';

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

    // Booth Management
    case VIEW_BOOTH = 'view-booth';
    case CREATE_BOOTH = 'create-booth';
    case UPDATE_BOOTH = 'update-booth';
    case DELETE_BOOTH = 'delete-booth';
    case BOOK_BOOTH = 'book-booth';

    // Booking
    case VIEW_BOOKING = 'view-booking';
    case CREATE_BOOKING = 'create-booking';
    case UPDATE_BOOKING = 'update-booking';
    case DELETE_BOOKING = 'delete-booking';


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

            self::VIEW_BOOTH         => 'View Booths',
            self::CREATE_BOOTH       => 'Create Booth',
            self::UPDATE_BOOTH       => 'Update Booth',
            self::DELETE_BOOTH       => 'Delete Booth',
            self::BOOK_BOOTH         => 'Book Booth',

            self::VIEW_BOOKING       => 'View Bookings',
            self::CREATE_BOOKING     => 'Create Booking',
            self::UPDATE_BOOKING     => 'Update Booking',
            self::DELETE_BOOKING     => 'Delete Booking',

            default                  => 'Unknown',
        };
    }
}
