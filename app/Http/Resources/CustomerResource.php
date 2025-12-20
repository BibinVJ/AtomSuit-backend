<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Customer
 */
class CustomerResource extends BaseResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            // 'total_spent' => $this->totalSpent(),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
