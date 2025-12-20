<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Subscription
 */
class SubscriptionResource extends BaseResource
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
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'name' => $this->name,
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_price' => $this->stripe_price,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at?->format('Y-m-d H:i:s'),
            'ends_at' => $this->ends_at?->format('Y-m-d H:i:s'),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'is_canceled' => $this->canceled(),
            'is_on_trial' => $this->onTrial(),
            'is_on_grace_period' => $this->onGracePeriod(),
            'items' => SubscriptionItemResource::collection($this->whenLoaded('items')),
            'invoices' => SubscriptionInvoiceResource::collection($this->whenLoaded('subscriptionInvoices')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
