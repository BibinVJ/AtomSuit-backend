<?php

namespace App\Enums;

enum PlanIntervalEnum: string
{
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';
    case LIFETIME = 'lifetime';
}
