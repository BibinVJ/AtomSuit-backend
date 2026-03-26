<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\CreditNote
 */
class CreditNoteResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'credit_note_number' => $this->credit_note_number,
            'credit_note_date' => $this->credit_note_date,
            'status' => $this->status,
            'sales_invoice_id' => $this->sales_invoice_id,
            'sales_invoice' => new SalesInvoiceResource($this->whenLoaded('salesInvoice')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'is_stock_returned' => $this->is_stock_returned,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'total_amount' => $this->total_amount,
            'customer_meta' => $this->customer_meta,
            'cost_center_id' => $this->cost_center_id,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'notes' => $this->notes,
            'items' => CreditNoteItemResource::collection($this->whenLoaded('items')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
