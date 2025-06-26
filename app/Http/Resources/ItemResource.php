<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ItemResource extends BaseResource
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
            'sku' => $this->sku,
            'name' => $this->name,
            // 'description' => $this->description,
            // 'is_active' => $this->is_active,
            // 'category' => new CategoryResource($this->whenLoaded('category')),
            // 'unit' => new UnitResource($this->whenLoaded('unit')),
            // 'type' => $this->type,
            'stock_on_hand' => $this->stockOnHand(),
            // 'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            // 'cogs_account' => new ChartOfAccountResource($this->whenLoaded('cogsAccount')),
            // 'inventory_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAccount')),
            // 'inventory_adjustment_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAdjustmentAccount')),
        ];
    }
}
