<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemPriceRequest extends FormRequest
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
            'price_list_id' => 'required|exists:price_lists,id',
            'item_id' => 'required|exists:items,id',
            'min_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],
            'price' => 'required|numeric|min:0',
        ];
    }
}
