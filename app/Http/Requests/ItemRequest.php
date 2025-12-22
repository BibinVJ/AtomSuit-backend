<?php

namespace App\Http\Requests;

use App\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ItemRequest extends FormRequest
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
            'sku' => ['required', 'string', 'max:255', new \App\Rules\UniqueInTrash('items', 'sku', $this->route('item')?->id)],
            'name' => 'required|string|max:255',
            'category_id' => ['required', \Illuminate\Validation\Rule::exists('categories', 'id')],
            'unit_id' => ['required', \Illuminate\Validation\Rule::exists('units', 'id')],
            'description' => 'nullable|string',
            'type' => ['required', new Enum(ItemType::class)],
            'selling_price' => 'nullable|numeric|min:0',
        ];
    }
}
