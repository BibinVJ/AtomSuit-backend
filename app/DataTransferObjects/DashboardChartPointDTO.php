<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class DashboardChartPointDTO
{
    public function __construct(
        public Carbon $date,
        public float $total
    ) {}
}
