<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\DeliveryNote
 */
class DeliveryNoteResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'dn_number' => $this->dn_number,
            'reference_number' => $this->reference_number,
            'sales_order_id' => $this->sales_order_id,
            'sales_order' => new SalesOrderResource($this->whenLoaded('salesOrder')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'dispatch_date' => $this->dispatch_date,
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
            'items' => DeliveryNoteItemResource::collection($this->whenLoaded('items')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
