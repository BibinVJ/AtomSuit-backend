<?php

namespace App\Enums;

enum TaxRateTypeEnum: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
