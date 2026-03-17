<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\PurchaseOrder
 */
class PurchaseOrderResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'reference_number' => $this->reference_number,
            'vendor_id' => $this->vendor_id,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'cost_center_id' => $this->cost_center_id,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'total_amount' => $this->items->sum(fn ($item) => $item->quantity * $item->unit_price),
            'created_by' => $this->created_by, // Could be UserResource if needed
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
