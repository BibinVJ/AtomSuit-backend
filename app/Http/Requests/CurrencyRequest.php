<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:3', new \App\Rules\UniqueInTrash('currencies', 'code', $this->route('currency')?->id)],
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:10',
            'is_default' => 'boolean',
            'thousand_separator' => 'nullable|string|size:1',
            'decimal_separator' => 'nullable|string|size:1',
            'precision' => 'nullable|integer|between:0,4',
            'symbol_position' => 'nullable|in:before,after',
        ];
    }
}
