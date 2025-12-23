<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new \App\Rules\UniqueInTrash('categories', 'name', $this->route('category'))],
            'description' => 'nullable|string',
            'sales_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'cogs_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'inventory_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'inventory_adjustment_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'purchase_account_id' => ['nullable', 'exists:chart_of_accounts,id'],
        ];
    }
}
