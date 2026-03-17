<?php

namespace App\Enums;

enum PurchaseInvoiceStatus: string
{
    case DRAFT = 'DRAFT';
    case POSTED = 'POSTED';
    case PAID = 'PAID';
    case VOID = 'VOID';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::POSTED => 'Posted',
            self::PAID => 'Paid',
            self::VOID => 'Void',
        };
    }
}
