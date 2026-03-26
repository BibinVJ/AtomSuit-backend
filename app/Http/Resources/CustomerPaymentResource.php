<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\CustomerPayment
 */
class CustomerPaymentResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'payment_date' => $this->payment_date,
            'amount' => $this->amount,
            'allocated_amount' => $this->allocated_amount,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'account_id' => $this->account_id,
            'account' => new ChartOfAccountResource($this->whenLoaded('account')),
            'cost_center_id' => $this->cost_center_id,
            'cost_center' => new CostCenterResource($this->whenLoaded('costCenter')),
            'status' => $this->status,
            'notes' => $this->notes,
            'allocations' => CustomerPaymentAllocationResource::collection($this->whenLoaded('allocations')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
