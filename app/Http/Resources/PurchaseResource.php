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
            'vendor' => VendorResource::make($this->vendor),
            'invoice_number' => $this->invoice_number,
            'purchase_date' => $this->purchase_date->toDateString(),
            'total_amount' => $this->total,
            'payment_status' => $this->payment_status,
            'items' => PurchaseItemResource::collection($this->items),
        ];
    }
}
