<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\User
 */
class TenantResource extends BaseResource
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
            'status' => $this->status,
            'trial_ends_at' => $this->trial_ends_at,
            'grace_period_ends_at' => $this->grace_period_ends_at,
            'domain_name' => new DomainResource($this->whenLoaded('domain')),
            // Use currentSubscription plan if available, otherwise fall back to direct plan relationship
            'current_plan' => new PlanResource(
                $this->currentSubscription?->plan ?? $this->whenLoaded('plan')
            ),
            'created_at' => $this->created_at,
        ];
    }
}
