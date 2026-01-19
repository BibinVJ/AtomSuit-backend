<?php

namespace App\Enums;

enum AccountClassEnum: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
