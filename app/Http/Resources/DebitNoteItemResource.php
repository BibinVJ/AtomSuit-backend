<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DebitNoteItemResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'debit_note_id' => $this->debit_note_id,
            'item_id' => $this->item_id,
            'item_meta' => $this->item_meta,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'discount_amount' => $this->discount_amount,
            'tax_group_id' => $this->tax_group_id,
            'is_stock_returned' => $this->is_stock_returned,
            'sub_total' => $this->sub_total,
            'tax_meta' => $this->tax_meta,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,

            'item' => ItemResource::make($this->whenLoaded('item')),
            'tax_group' => TaxGroupResource::make($this->whenLoaded('taxGroup')),
        ];
    }
}
