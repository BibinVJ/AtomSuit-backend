<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Sale
 */
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
            'id' => $this->id,
            'customer' => CustomerResource::make($this->customer),
            'invoice_number' => $this->invoice_number,
            'sale_date' => $this->sale_date->toDateString(),
            'total_amount' => $this->total,
            'payment_status' => $this->payment_status,
            'items' => SaleItemResource::collection($this->items),
        ];
    }
}
