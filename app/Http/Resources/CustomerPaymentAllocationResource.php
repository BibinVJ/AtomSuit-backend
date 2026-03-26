<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\CustomerPaymentAllocation
 */
class CustomerPaymentAllocationResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_payment_id' => $this->customer_payment_id,
            'sales_invoice_id' => $this->sales_invoice_id,
            'sales_invoice' => new SalesInvoiceResource($this->whenLoaded('salesInvoice')),
            'allocated_amount' => $this->allocated_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
