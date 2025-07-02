<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DashboardResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'metrics' => [
                'total_sales_amount' => $this->resource->total_sales_amount,
                'total_purchase_amount' => $this->resource->total_purchase_amount,
            ],
            'top_items' => [
                'sold' => $this->resource->top_selling_items,
                'purchased' => $this->resource->top_purchased_items,
            ],
            'stock_alerts' => [
                'expiring_items' => $this->resource->expiring_items,
                'out_of_stock_items' => $this->resource->out_of_stock_items,
                'low_stock_items' => $this->resource->low_stock_items,
                'dead_stock_items' => $this->resource->dead_stock_items,
            ],
            'customers' => [
                'best_customers' => $this->resource->best_customers,
            ],
            'charts' => [
                'sales' => $this->resource->sales_chart_data,
                'purchases' => $this->resource->purchase_chart_data,
            ],
        ];
    }
}
