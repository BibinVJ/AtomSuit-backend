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
            'sku' => 'required|string|max:255|unique:items,sku,' . $this->route('item')?->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'description' => 'nullable|string',
            'type' => ['required', new Enum(ItemType::class)],
            'sales_account_id' => 'required|exists:chart_of_accounts,id',
            'cogs_account_id' => 'required|exists:chart_of_accounts,id',
            'inventory_account_id' => 'required|exists:chart_of_accounts,id',
            'inventory_adjustment_account_id' => 'required|exists:chart_of_accounts,id',
            'is_active' => 'boolean',
        ];
    }
}
