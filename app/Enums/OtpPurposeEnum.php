<?php

namespace App\Enums;

enum OtpPurposeEnum: string
{
    case PASSWORD_RESET = 'password_reset';
    case ORDER_VERIFICATION = 'order_verification';
    case TWO_FACTOR = 'two_factor';
}
