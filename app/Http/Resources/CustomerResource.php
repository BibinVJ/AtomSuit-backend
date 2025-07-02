<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

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
            // 'total_spent' => $this->totalSpent(),
            'is_active' => $this->is_active,
        ];
    }
}
