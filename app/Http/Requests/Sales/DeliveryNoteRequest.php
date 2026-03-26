<?php

namespace App\Http\Requests\Sales;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'customer_id' => ['required_without:sales_order_id', 'exists:customers,id'],
            'dn_number' => ['required', 'string', 'max:255', 'unique:delivery_notes,dn_number'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'dispatch_date' => ['required', 'date'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.dispatched_quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
