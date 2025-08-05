<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Batch
 */
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
            'batch_number' => $this->batch_number,
            'manufacture_date' => $this->manufacture_date,
            'expiry_date' => $this->expiry_date,
            'cost_price' => $this->cost_price,
            'stock_on_hand' => $this->stockOnHand(),
        ];
    }
}
