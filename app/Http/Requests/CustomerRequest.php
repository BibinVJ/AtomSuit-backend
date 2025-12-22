<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new \App\Rules\UniqueInTrash('customers', 'name', $this->route('customer')?->id)],
            'email' => [
                'nullable',
                'email',
                'max:255',
                new \App\Rules\UniqueInTrash('customers', 'email', $this->route('customer')?->id),
                'required_without:phone',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                new \App\Rules\UniqueInTrash('customers', 'phone', $this->route('customer')?->id),
                'required_without:email',
            ],
            'currency_id' => ['required', \Illuminate\Validation\Rule::exists('currencies', 'id')],
            'sales_account_id' => ['required', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'sales_discount_account_id' => ['required', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'receivables_account_id' => ['required', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
            'sales_return_account_id' => ['required', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')],
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

    public function messages(): array
    {
        return [
            'email.required_without' => 'Either an email or phone number is required.',
            'phone.required_without' => 'Either a phone number or email is required.',
        ];
    }
}
