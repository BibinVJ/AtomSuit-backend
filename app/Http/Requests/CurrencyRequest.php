<?php

namespace App\Http\Requests;

use App\Rules\UniqueInTrash;
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
            'code' => ['required', 'string', 'size:3', new UniqueInTrash('currencies', 'code', $this->route('currency')?->id)],
            'name' => 'required|string|max:255',
            'symbol' => 'nullable|string|max:10',
        ];
    }
}
