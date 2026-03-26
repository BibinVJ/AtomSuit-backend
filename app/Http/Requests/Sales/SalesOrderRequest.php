<?php

namespace App\Http\Requests\Sales;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $salesOrder = $this->route('sales_order');
        $salesOrderId = $salesOrder?->id;

        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'order_number' => ['required', 'string', 'max:255', Rule::unique('sales_orders', 'order_number')->ignore($salesOrderId)],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
