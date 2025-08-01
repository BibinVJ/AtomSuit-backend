<?php

namespace App\DataTransferObjects;

class DashboardStockItemDTO
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $name,
        public int $stock_remaining
    ) {}
}
