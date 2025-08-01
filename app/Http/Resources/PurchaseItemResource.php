<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PurchaseItemResource extends BaseResource
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
            'item' => ItemResource::make($this->item),
            'batch' => BatchResource::make($this->batch),
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->quantity * $this->unit_cost,
        ];
    }
}
