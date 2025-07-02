<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class DashboardExpiringItemDTO
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $name,
        public string $batch_number,
        public ?Carbon $expiry_date,
        public int $stock_remaining
    ) {}
}
