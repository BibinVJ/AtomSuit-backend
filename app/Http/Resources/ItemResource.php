<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Item
 */
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
            'description' => $this->description,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'type' => $this->type,
            'stock_on_hand' => $this->stockOnHand(),
            'non_expired_stock' => $this->nonExpiredStock(),
            'expired_stock' => $this->expiredStock(),
            'batches' => BatchResource::collection($this->whenLoaded('batches')),
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'cogs_account' => new ChartOfAccountResource($this->whenLoaded('cogsAccount')),
            'inventory_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAccount')),
            'inventory_adjustment_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAdjustmentAccount')),
            'tax_group' => new TaxGroupResource($this->whenLoaded('taxGroup')),
            'item_prices' => ItemPriceResource::collection($this->whenLoaded('itemPrices')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
