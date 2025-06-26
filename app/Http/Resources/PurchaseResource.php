<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PurchaseResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'purchase_date' => $this->purchase_date->toDateString(),
            'total' => $this->total,
            'payment_status' => $this->payment_status,
            'items' => $this->items->map(fn($item) => [
                'id'         => $item->id,
                'item'       => $item->item->name,
                'batch'      => $item->batch?->batch_no,
                'quantity'   => $item->quantity,
                'unit_cost' => $item->unit_cost,
                'total_cost' => $item->quantity * $item->unit_cost,
            ]),
        ];
    }
}
