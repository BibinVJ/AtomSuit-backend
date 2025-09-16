<?php

namespace App\Http\Resources;

use App\Enums\RolesEnum;
use Illuminate\Http\Request;

/**
 * @mixin \App\Models\User
 */
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
            'phone' => $this->phone,
            'phone_verified_at' => $this->phone_verified_at,
            'status' => $this->status,
            'status_updated_at' => $this->status_updated_at,

            'is_admin' => $this->hasRole(RolesEnum::ADMIN->value),
            'role' => $this->roles->isNotEmpty()
                ? new RoleResource($this->roles->first()->withoutRelations())
                : null,
            'permission_names' => $this->getAllPermissions()->pluck('name'),

            'alternate_email' => $this->detail?->alternate_email,
            'alternate_phone' => $this->detail?->alternate_phone,
            'id_proof_type' => $this->detail?->id_proof_type,
            'id_proof_number' => $this->detail?->id_proof_number,
            'dob' => optional($this->detail?->dob)->format('Y-m-d'),
            'gender' => $this->detail?->gender,
            'profile_image' => $this->detail?->profile_image,

            'addresses' => AddressResource::collection($this->whenLoaded('addresses')),
            'social_links' => SocialLinkResource::collection($this->whenLoaded('socialLinks')),

            'created_at' => $this->created_at,
        ];
    }
}
