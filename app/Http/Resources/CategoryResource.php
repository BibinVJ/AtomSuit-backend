<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Category
 */
class CategoryResource extends BaseResource
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
            'description' => $this->description,
            'sales_account_id' => $this->sales_account_id,
            'cogs_account_id' => $this->cogs_account_id,
            'inventory_account_id' => $this->inventory_account_id,
            'inventory_adjustment_account_id' => $this->inventory_adjustment_account_id,
            'purchase_account_id' => $this->purchase_account_id,
            'deleted_at' => $this->deleted_at,
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'cogs_account' => new ChartOfAccountResource($this->whenLoaded('cogsAccount')),
            'inventory_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAccount')),
            'inventory_adjustment_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAdjustmentAccount')),
            'purchase_account' => new ChartOfAccountResource($this->whenLoaded('purchaseAccount')),
        ];
    }
}
