<?php

namespace App\Http\Requests\Sales;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_invoice_id' => ['nullable', 'exists:sales_invoices,id'],
            'customer_id' => ['required_without:sales_invoice_id', 'exists:customers,id'],
            'credit_note_number' => ['required', 'string', 'max:255', 'unique:credit_notes,credit_note_number'],
            'credit_note_date' => ['required', 'date'],
            'is_stock_returned' => ['boolean'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.sales_invoice_item_id' => ['nullable', 'exists:sales_invoice_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.returned_quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
