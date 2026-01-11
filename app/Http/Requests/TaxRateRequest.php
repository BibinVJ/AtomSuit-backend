<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                new \App\Rules\UniqueInTrash('tax_rates', 'name', $this->route('tax_rate')?->id),
            ],
            'rate' => 'required|numeric|min:0',
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'sales_account_id' => ['nullable', Rule::exists('chart_of_accounts', 'id')],
            'purchase_account_id' => ['nullable', Rule::exists('chart_of_accounts', 'id')],
        ];
    }
}
