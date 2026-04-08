<?php

namespace App\Http\Requests\Purchase;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $purchaseOrder = $this->route('purchase_order');
        $purchaseOrderId = $purchaseOrder?->id;

        $rules = [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'order_number' => ['required', 'string', 'max:255', Rule::unique('purchase_orders', 'order_number')->ignore($purchaseOrderId)],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['nullable', \Illuminate\Validation\Rule::enum(\App\Enums\PurchaseOrderStatus::class)],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];

        return $rules;
    }
}
