<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class VendorPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'exists:vendors,id'],
            'account_id' => ['required', 'exists:chart_of_accounts,id'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],

            // Allocations (allow multiple invoices to be paid simultaneously)
            'allocations' => ['nullable', 'array'],
            'allocations.*.purchase_invoice_id' => ['required', 'exists:purchase_invoices,id'],
            'allocations.*.allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
