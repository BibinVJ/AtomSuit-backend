<?php

namespace App\Enums;

enum AddressEnum: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case BILLING = 'billing';
    case SHIPPING = 'shipping';
}
