<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'account_id' => ['required', 'exists:chart_of_accounts,id'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'payment_number' => ['nullable', 'string', 'max:255', 'unique:customer_payments,payment_number'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'allocations' => ['nullable', 'array'],
            'allocations.*.sales_invoice_id' => ['required', 'exists:sales_invoices,id'],
            'allocations.*.allocated_amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
