<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'max:50',
                new \App\Rules\UniqueInTrash('chart_of_accounts', 'code', $this->route('chart_of_account')),
            ],
            'account_group_id' => [
                'required',
                Rule::exists('account_groups', 'id'),
            ],
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
        ];
    }
}
