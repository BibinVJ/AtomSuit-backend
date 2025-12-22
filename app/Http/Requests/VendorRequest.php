<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new \App\Rules\UniqueInTrash('vendors', 'name', $this->route('vendor')?->id)],
            'email' => ['nullable', 'email', 'max:255', new \App\Rules\UniqueInTrash('vendors', 'email', $this->route('vendor')?->id)],
            'phone' => ['nullable', 'string', 'max:20', new \App\Rules\UniqueInTrash('vendors', 'phone', $this->route('vendor')?->id)],
            'currency_id' => ['required', \Illuminate\Validation\Rule::exists('currencies', 'id')],
            'payables_account_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'purchase_account_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'purchase_discount_account_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'purchase_return_account_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'billing_address_line_1' => 'nullable|string|max:255',
            'billing_address_line_2' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            'billing_zip_code' => 'nullable|string|max:20',
            'shipping_address_line_1' => 'nullable|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            'shipping_zip_code' => 'nullable|string|max:20',
        ];
    }
}
