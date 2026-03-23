<?php

namespace App\Enums;

enum VendorPaymentStatus: string
{
    case POSTED = 'POSTED';
    case VOIDED = 'VOIDED';
}
