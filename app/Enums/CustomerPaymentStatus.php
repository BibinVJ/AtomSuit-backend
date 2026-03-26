<?php

namespace App\Enums;

enum CustomerPaymentStatus: string
{
    case POSTED = 'POSTED';
    case VOIDED = 'VOIDED';

    public function label(): string
    {
        return match ($this) {
            self::POSTED => 'Posted',
            self::VOIDED => 'Voided',
        };
    }
}
