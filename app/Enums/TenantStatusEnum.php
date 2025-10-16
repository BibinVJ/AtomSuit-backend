<?php

namespace App\Enums;

enum TenantStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
}
