<?php

namespace App\Http\Requests\Purchase;

use App\Enums\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $invoiceId = $this->route('purchase_invoice')?->id;

        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'grn_id' => ['nullable', 'exists:goods_received_notes,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('purchase_invoices', 'invoice_number')->ignore($invoiceId),
            ],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'posting_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:posting_date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.goods_received_note_item_id' => ['nullable', 'exists:goods_received_note_items,id'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
