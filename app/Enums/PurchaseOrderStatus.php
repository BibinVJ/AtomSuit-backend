<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case CONFIRMED = 'CONFIRMED';
    case PARTIALLY_RECEIVED = 'PARTIALLY_RECEIVED';
    case RECEIVED = 'RECEIVED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::CONFIRMED => 'Confirmed',
            self::PARTIALLY_RECEIVED => 'Partially Received',
            self::RECEIVED => 'Received',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }
}
