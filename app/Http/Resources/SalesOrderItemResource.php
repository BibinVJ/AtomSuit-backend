<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\SalesOrderItem
 */
class SalesOrderItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sales_order_id' => $this->sales_order_id,
            'item_id' => $this->item_id,
            'item' => new ItemResource($this->whenLoaded('item')),
            'item_meta' => $this->item_meta,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'sub_total' => $this->sub_total,
            'tax_meta' => $this->tax_meta,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'tax_group_id' => $this->tax_group_id,
            'tax_group' => new TaxGroupResource($this->whenLoaded('taxGroup')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
