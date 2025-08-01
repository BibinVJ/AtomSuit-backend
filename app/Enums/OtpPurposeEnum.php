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
            self::PASSWORD_RESET => 'Password Reset',
            self::ORDER_VERIFICATION => 'Order Verfication',
            self::TWO_FACTOR => 'Two Factor'
        };
    }
}
