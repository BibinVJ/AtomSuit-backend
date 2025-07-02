<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SaleItemResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'item'        => ItemResource::make($this->item),
            'batches'     => BatchResource::collection($this->batches),
            'quantity'    => $this->quantity,
            'unit_price'  => $this->unit_price,
            'total_price' => $this->quantity * $this->unit_price,
        ];
    }
}
