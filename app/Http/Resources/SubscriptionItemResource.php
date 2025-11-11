<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \Laravel\Cashier\SubscriptionItem
 */
class SubscriptionItemResource extends BaseResource
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
            'subscription_id' => $this->subscription_id,
            'stripe_id' => $this->stripe_id,
            'stripe_product' => $this->stripe_product,
            'stripe_price' => $this->stripe_price,
            'quantity' => $this->quantity,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
