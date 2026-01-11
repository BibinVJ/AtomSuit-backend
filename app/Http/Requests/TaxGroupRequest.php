<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxGroupRequest extends FormRequest
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
                new \App\Rules\UniqueInTrash('tax_groups', 'name', $this->route('tax_group')?->id),
            ],
            'tax_rates' => 'nullable|array',
            'tax_rates.*' => 'exists:tax_rates,id',
        ];
    }
}
