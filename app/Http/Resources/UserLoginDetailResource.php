<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\UserLoginDetail
 */
class UserLoginDetailResource extends BaseResource
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
            'user_id' => $this->user_id,
            'token_id' => $this->token_id,
            'login_at' => $this->login_at,
            'logout_at' => $this->logout_at,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'login_method' => $this->login_method,
            'city' => $this->city,
            'country' => $this->country,
            'iso_code' => $this->iso_code,
            'os' => $this->os,
            'browser' => $this->browser,
            'device_type' => $this->device_type,

            'user' => new UserResource($this->whenLoaded('user')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
