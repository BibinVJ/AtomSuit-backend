<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ItemPriceResource extends BaseResource
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
            'price_list_id' => $this->price_list_id,
            'price_list' => new PriceListResource($this->whenLoaded('priceList')),
            'item_id' => $this->item_id,
            'item' => new \App\Http\Resources\ItemResource($this->whenLoaded('item')), // Assuming ItemResource exists
            'min_quantity' => $this->min_quantity,
            'price' => $this->price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
