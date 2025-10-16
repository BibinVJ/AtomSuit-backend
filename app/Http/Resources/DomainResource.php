<?php

namespace App\Http\Resources;

use App\Enums\RolesEnum;
use Illuminate\Http\Request;

/**
 * @mixin \App\Models\User
 */
class DomainResource extends BaseResource
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
            'domain' => $this->domain,
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
        ];
    }
}
