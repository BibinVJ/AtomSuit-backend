<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\SalesOrder
 */
class SalesOrderResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'reference_number' => $this->reference_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'total_amount' => $this->total_amount,
            'customer_meta' => $this->customer_meta,
            'cost_center_id' => $this->cost_center_id,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'items' => SalesOrderItemResource::collection($this->whenLoaded('items')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
