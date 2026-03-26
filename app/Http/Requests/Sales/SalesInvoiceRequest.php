<?php

namespace App\Http\Requests\Sales;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_note_id' => ['nullable', 'exists:delivery_notes,id'],
            'sales_order_id' => ['nullable', 'exists:sales_orders,id'],
            'customer_id' => ['required_without_all:delivery_note_id,sales_order_id', 'exists:customers,id'],
            'invoice_number' => ['required', 'string', 'max:255', 'unique:sales_invoices,invoice_number'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.delivery_note_item_id' => ['nullable', 'exists:delivery_note_items,id'],
            'items.*.sales_order_item_id' => ['nullable', 'exists:sales_order_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
