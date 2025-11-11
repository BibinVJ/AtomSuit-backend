<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\PlanFeature
 */
class PlanFeatureResource extends BaseResource
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
            'key' => $this->feature_key,
            'value' => $this->value, // Uses accessor for type casting
            'display_name' => $this->display_name,
            'description' => $this->description,
            'type' => $this->feature_type,
            'display_order' => $this->display_order,
        ];
    }
}
