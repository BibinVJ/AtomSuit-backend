<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * @mixin \App\Models\Customer
 */
class CustomerResource extends BaseResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'sales_account' => new ChartOfAccountResource($this->whenLoaded('salesAccount')),
            'sales_discount_account' => new ChartOfAccountResource($this->whenLoaded('salesDiscountAccount')),
            'receivables_account' => new ChartOfAccountResource($this->whenLoaded('receivablesAccount')),
            'sales_return_account' => new ChartOfAccountResource($this->whenLoaded('salesReturnAccount')),
            'billing_address_line_1' => $this->billing_address_line_1,
            'billing_address_line_2' => $this->billing_address_line_2,
            'billing_city' => $this->billing_city,
            'billing_state' => $this->billing_state,
            'billing_country' => $this->billing_country,
            'billing_zip_code' => $this->billing_zip_code,
            'shipping_address_line_1' => $this->shipping_address_line_1,
            'shipping_address_line_2' => $this->shipping_address_line_2,
            'shipping_city' => $this->shipping_city,
            'shipping_state' => $this->shipping_state,
            'shipping_country' => $this->shipping_country,
            'shipping_zip_code' => $this->shipping_zip_code,
            // 'total_spent' => $this->totalSpent(),
            'deleted_at' => $this->deleted_at,
        ];
    }
}
