<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends UserResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get parent's fields
        $data = parent::toArray($request);

        $data['permissions'] = PermissionResource::collection($this->getAllPermissions());

        return $data;
    }
}
