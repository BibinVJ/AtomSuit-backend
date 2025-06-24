<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseResource
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
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status->value,
            'status_updated_at' => $this->status_updated_at,

            'is_admin' => $this->hasRole('admin'),
            'role_names' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'profile_image' => $this->profile_image,
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
        ];
    }
}
