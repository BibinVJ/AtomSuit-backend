<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AccountGroupResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'account_type' => new AccountTypeResource($this->whenLoaded('accountType')),
            'parent' => new AccountGroupResource($this->whenLoaded('parent')),
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
