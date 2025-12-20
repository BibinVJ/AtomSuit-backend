<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\User
 */
class PlanResource extends BaseResource
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
            'price' => $this->price,
            'interval' => $this->interval,
            'interval_count' => $this->interval_count,
            'is_trial_plan' => $this->is_trial_plan,
            'trial_duration_in_days' => $this->trial_duration_in_days,
            'is_expired_user_plan' => $this->is_expired_user_plan,
            'features' => PlanFeatureResource::collection($this->whenLoaded('features')),
            'subscribed_tenants' => TenantResource::collection($this->whenLoaded('subscribedTenants')),
        ];
    }
}
