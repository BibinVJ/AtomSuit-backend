<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseInvoiceItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_invoice_id' => $this->purchase_invoice_id,
            'goods_received_note_item_id' => $this->goods_received_note_item_id,
            'item_id' => $this->item_id,
            'item_meta' => $this->item_meta,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'tax_group_id' => $this->tax_group_id,
            'sub_total' => $this->sub_total,
            'tax_meta' => $this->tax_meta,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,

            'item' => ItemResource::make($this->whenLoaded('item')),
            'tax_group' => TaxGroupResource::make($this->whenLoaded('taxGroup')),
        ];
    }
}
