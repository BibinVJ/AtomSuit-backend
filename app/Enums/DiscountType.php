<?php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
