<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ChartOfAccountResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'account_group' => new AccountGroupResource($this->whenLoaded('accountGroup')),
            'description' => $this->description,
            'opening_balance' => $this->opening_balance,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
