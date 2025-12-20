<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CurrencyResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'is_default' => $this->is_default,
            'thousand_separator' => $this->thousand_separator,
            'decimal_separator' => $this->decimal_separator,
            'precision' => $this->precision,
            'symbol_position' => $this->symbol_position,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
