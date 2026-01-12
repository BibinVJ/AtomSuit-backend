<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PriceListResource extends BaseResource
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
            'code' => $this->code,
            'type' => $this->type,
            'currency_id' => $this->currency_id,
            'currency' => new \App\Http\Resources\CurrencyResource($this->whenLoaded('currency')), // Assuming CurrencyResource exists
            'is_tax_inclusive' => (bool) $this->is_tax_inclusive,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
