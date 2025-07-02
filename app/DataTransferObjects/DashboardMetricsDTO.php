<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Collection;

class DashboardMetricsDTO
{
    public function __construct(
        public float $total_sales_amount,
        public float $total_purchase_amount,
        public Collection $top_selling_items,
        public Collection $top_purchased_items,
        public Collection $expiring_items,
        public Collection $out_of_stock_items,
        public Collection $low_stock_items,
        public Collection $dead_stock_items,
        public Collection $best_customers,
        public array $sales_chart_data,
        public array $purchase_chart_data,
    ) {}
}
