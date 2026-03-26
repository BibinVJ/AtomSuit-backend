<?php

namespace App\Http\Requests\Purchase;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DebitNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $debitNoteId = $this->route('debit_note')?->id;

        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'purchase_invoice_id' => ['nullable', 'exists:purchase_invoices,id'],
            'debit_note_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('debit_notes', 'debit_note_number')->ignore($debitNoteId),
            ],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
            'items.*.is_stock_returned' => ['boolean'],
        ];
    }
}
