<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DebitNoteResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'purchase_invoice_id' => $this->purchase_invoice_id,
            'debit_note_number' => $this->debit_note_number,
            'reference_number' => $this->reference_number,
            'date' => $this->date,
            'status' => $this->status,
            'notes' => $this->notes,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'total_amount' => $this->total_amount,
            'vendor_meta' => $this->vendor_meta,
            'warehouse_id' => $this->warehouse_id,
            'cost_center_id' => $this->cost_center_id,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,

            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'purchase_invoice' => PurchaseInvoiceResource::make($this->whenLoaded('purchaseInvoice')),
            'cost_center' => CostCenterResource::make($this->whenLoaded('costCenter')),
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'items' => DebitNoteItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
