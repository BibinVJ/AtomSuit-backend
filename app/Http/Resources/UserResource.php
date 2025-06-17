<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

            'last_login_at' => $this->last_login_at,
            'last_login_ip' => $this->last_login_ip,
        ];
    }
}
