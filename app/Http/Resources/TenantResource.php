<?php

namespace App\Http\Resources;

use App\Enums\RolesEnum;
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
            'current_plan' => new PlanResource(optional($this->currentSubscription?->plan)),
        ];
    }
}
