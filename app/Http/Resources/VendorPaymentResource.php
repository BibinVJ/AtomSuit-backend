<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class VendorPaymentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'vendor_id' => $this->vendor_id,
            'account_id' => $this->account_id,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'account' => ChartOfAccountResource::make($this->whenLoaded('account')),
            'allocations' => VendorPaymentAllocationResource::collection($this->whenLoaded('allocations')),
        ];
    }
}
