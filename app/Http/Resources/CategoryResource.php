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
            'tax_group' => new TaxGroupResource($this->whenLoaded('taxGroup')),
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'cogs_account' => new ChartOfAccountResource($this->whenLoaded('cogsAccount')),
            'inventory_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAccount')),
            'inventory_adjustment_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAdjustmentAccount')),
            'purchase_account' => new ChartOfAccountResource($this->whenLoaded('purchaseAccount')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
