<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\TaxRate
 */
class TaxRateResource extends BaseResource
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
            'name' => $this->name,
            'rate' => $this->rate,
            'type' => $this->type,
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'purchase_account' => new ChartOfAccountResource($this->whenLoaded('purchaseAccount')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
