<?php

namespace App\DataTransferObjects;

use Carbon\Carbon;

class DashboardStockItemDTO
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $name,
        public int $stock_remaining
    ) {}
}
