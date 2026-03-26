<?php

namespace App\Enums;

enum SalesInvoiceStatus: string
{
    case POSTED = 'POSTED';
    case PARTIALLY_PAID = 'PARTIALLY_PAID';
    case PAID = 'PAID';
    case VOIDED = 'VOIDED';

    public function label(): string
    {
        return match ($this) {
            self::POSTED => 'Posted',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAID => 'Paid',
            self::VOIDED => 'Voided',
        };
    }
}
