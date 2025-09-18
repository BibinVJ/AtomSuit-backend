<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatusEnum;
use App\Enums\TransactionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'invoice_number' => 'required|unique:purchases,invoice_number,'.$this->route('purchase')?->id,
            'purchase_date' => 'required|date',
            'status' => ['nullable', new Enum(TransactionStatus::class)],
            'payment_status' => ['nullable', new Enum(PaymentStatusEnum::class)],
            'note' => 'nullable',

            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.batch_number' => 'required',
            'items.*.expiry_date' => 'nullable|date|after:purchase_date',
            'items.*.manufacture_date' => 'nullable|date|before:purchase_date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.description' => 'nullable',
        ];
    }
}
