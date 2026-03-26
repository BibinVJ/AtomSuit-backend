<?php

namespace App\Enums;

enum SalesOrderStatus: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case CONFIRMED = 'CONFIRMED';
    case PARTIALLY_DELIVERED = 'PARTIALLY_DELIVERED';
    case DELIVERED = 'DELIVERED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::CONFIRMED => 'Confirmed',
            self::PARTIALLY_DELIVERED => 'Partially Delivered',
            self::DELIVERED => 'Delivered',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
