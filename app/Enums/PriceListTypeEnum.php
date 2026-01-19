<?php

namespace App\Enums;

enum PriceListTypeEnum: string
{
    case SALES = 'sales';
    case PURCHASE = 'purchase';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
