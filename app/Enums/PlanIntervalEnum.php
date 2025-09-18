<?php

namespace App\Enums;

enum PlanIntervalEnum: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
    case LIFETIME = 'lifetime';

}
