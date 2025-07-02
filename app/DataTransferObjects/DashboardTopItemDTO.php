<?php

namespace App\DataTransferObjects;

class DashboardTopItemDTO
{
    public function __construct(
        public int $id,
        public string $sku,
        public string $name,
        public int $total_quantity
    ) {}
}
