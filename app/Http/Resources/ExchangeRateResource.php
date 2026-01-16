<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ExchangeRateResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'base_currency' => new CurrencyResource($this->whenLoaded('baseCurrency')),
            'target_currency' => new CurrencyResource($this->whenLoaded('targetCurrency')),
            'rate' => $this->rate,
            'effective_date' => $this->effective_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
