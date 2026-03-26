<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\SalesInvoice
 */
class SalesInvoiceResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_date' => $this->invoice_date,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
            'due_amount' => $this->due_amount,
            'cost_center_id' => $this->cost_center_id,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'notes' => $this->notes,
            'items' => SalesInvoiceItemResource::collection($this->whenLoaded('items')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
