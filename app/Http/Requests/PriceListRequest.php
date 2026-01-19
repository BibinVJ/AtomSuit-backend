<?php

namespace App\Http\Requests;

use App\Rules\UniqueInTrash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceListRequest extends FormRequest
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
        $priceList = $this->route('price_list');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueInTrash('price_lists', 'name', $priceList?->id),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                new UniqueInTrash('price_lists', 'code', $priceList?->id),
            ],
            'currency_id' => 'required|exists:currencies,id',
            'type' => ['required', Rule::in(['sales', 'purchase'])],
            'is_tax_inclusive' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }
}
