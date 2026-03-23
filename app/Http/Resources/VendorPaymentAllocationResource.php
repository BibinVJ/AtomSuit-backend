<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class VendorPaymentAllocationResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_payment_id' => $this->vendor_payment_id,
            'purchase_invoice_id' => $this->purchase_invoice_id,
            'allocated_amount' => $this->allocated_amount,

            'purchase_invoice' => PurchaseInvoiceResource::make($this->whenLoaded('purchaseInvoice')),
        ];
    }
}
