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
            'deleted_at' => $this->deleted_at,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'type' => $this->type,
            'stock_on_hand' => $this->stockOnHand(),
            'non_expired_stock' => $this->nonExpiredStock(),
            'expired_stock' => $this->expiredStock(),
            'batches' => BatchResource::collection($this->whenLoaded('batches')),
            'sales_account_id' => $this->sales_account_id,
            'cogs_account_id' => $this->cogs_account_id,
            'inventory_account_id' => $this->inventory_account_id,
            'inventory_adjustment_account_id' => $this->inventory_adjustment_account_id,
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'cogs_account' => new ChartOfAccountResource($this->whenLoaded('cogsAccount')),
            'inventory_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAccount')),
            'inventory_adjustment_account' => new ChartOfAccountResource($this->whenLoaded('inventoryAdjustmentAccount')),
            'tax_group_id' => $this->tax_group_id,
            'tax_group' => new TaxGroupResource($this->whenLoaded('taxGroup')),
            'item_prices' => ItemPriceResource::collection($this->whenLoaded('itemPrices')),
        ];
    }
}
