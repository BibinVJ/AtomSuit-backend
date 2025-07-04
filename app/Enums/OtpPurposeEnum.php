<?php

namespace App\Enums;

enum OtpPurposeEnum: string
{
    case PASSWORD_RESET = 'password_reset';
    case ORDER_VERIFICATION = 'order_verification';
    case TWO_FACTOR = 'two_factor';

    public function label(): string
    {
        return match ($this) {
            static::PASSWORD_RESET => 'Password Reset',
            static::ORDER_VERIFICATION => 'Order Verfication',
            static::TWO_FACTOR => 'Two Factor'
        };
    }
}
