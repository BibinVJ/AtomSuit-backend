<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \Spatie\Permission\Models\Permission
 */
class PermissionResource extends BaseResource
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
            'guard_name' => $this->guard_name,
            'created_at' => $this->created_at,
        ];
    }
}
