<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodsReceivedNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grnId = $this->route('goods_received_note')?->id;

        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'grn_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('goods_received_notes', 'grn_number')->ignore($grnId),
            ],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'notes' => ['nullable', 'string'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0.0001'],
            'items.*.accepted_quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.rejected_quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_group_id' => ['nullable', 'exists:tax_groups,id'],
        ];
    }
}
