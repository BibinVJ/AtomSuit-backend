<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class BatchResource extends BaseResource
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
            'item_id' => $this->item_id,
            'batch_no' => $this->batch_no,
            'manufacture_date' => $this->manufacture_date,
            'expiry_date' => $this->expiry_date,
            'cost_price' => $this->cost_price,
        ];
    }
}
