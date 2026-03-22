<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PurchaseInvoiceResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'grn_id' => $this->grn_id,
            'purchase_order_id' => $this->purchase_order_id,
            'vendor_id' => $this->vendor_id,
            'invoice_number' => $this->invoice_number,
            'reference_number' => $this->reference_number,
            'posting_date' => $this->posting_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'cost_center_id' => $this->cost_center_id,
            'warehouse_id' => $this->warehouse_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'grn' => GoodsReceivedNoteResource::make($this->whenLoaded('grn')),
            'purchase_order' => PurchaseOrderResource::make($this->whenLoaded('purchaseOrder')),
            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'cost_center' => CostCenterResource::make($this->whenLoaded('costCenter')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'items' => PurchaseInvoiceItemResource::collection($this->whenLoaded('items')),

            'total_amount' => $this->items->sum('total_amount'),
        ];
    }
}
