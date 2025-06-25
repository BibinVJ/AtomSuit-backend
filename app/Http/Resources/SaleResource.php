<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SaleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'customer'       => $this->customer?->name,
            'invoice_number' => $this->invoice_number,
            'sale_date'      => $this->sale_date->toDateString(),
            'payment_status' => $this->payment_status,
            'items'          => $this->items->map(fn($item) => [
                'id'          => $item->id,
                'item'        => $item->item->name,
                'batch'       => $item->batch?->batch_no,
                'quantity'   => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->quantity * $item->unit_price,
            ]),
        ];
    }
}
