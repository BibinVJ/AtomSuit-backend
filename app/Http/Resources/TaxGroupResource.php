<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\TaxGroup
 */
class TaxGroupResource extends BaseResource
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
            'tax_rates' => TaxRateResource::collection($this->whenLoaded('taxRates')),
            'total_rate' => $this->whenLoaded('taxRates', function () {
                return $this->taxRates->sum('rate'); // Assuming simplified percentage addition for display
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
