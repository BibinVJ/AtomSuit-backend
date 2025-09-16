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
            'user' => UserResource::make($this->user),
            'customer' => CustomerResource::make($this->customer),
            'invoice_number' => $this->invoice_number,
            'sale_date' => $this->sale_date->toDateString(),
            'total_amount' => $this->total,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'note' => $this->note,
            'items' => SaleItemResource::collection($this->items),
        ];
    }
}
