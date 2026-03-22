<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class GoodsReceivedNoteResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'vendor_id' => $this->vendor_id,
            'grn_number' => $this->grn_number,
            'reference_number' => $this->reference_number,
            'received_date' => $this->received_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'cost_center_id' => $this->cost_center_id,
            'warehouse_id' => $this->warehouse_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'purchase_order' => PurchaseOrderResource::make($this->whenLoaded('purchaseOrder')),
            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'cost_center' => CostCenterResource::make($this->whenLoaded('costCenter')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'items' => GoodsReceivedNoteItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
