<?php

namespace App\Http\Requests;

use App\Enums\ItemType;
use App\Rules\UniqueInTrash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'sku' => ['required', 'string', 'max:255', new UniqueInTrash('items', 'sku', $this->route('item')?->id)],
            'name' => 'required|string|max:255',
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'unit_id' => ['required', Rule::exists('units', 'id')],
            'description' => 'nullable|string',
            'type' => ['required', new Enum(ItemType::class)],
            'sales_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'cogs_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'inventory_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'inventory_adjustment_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'tax_group_id' => ['required', Rule::exists('tax_groups', 'id')],
            'prices' => ['sometimes', 'array'],
            'prices.*.id' => ['sometimes', 'integer', 'exists:item_prices,id'],
            'prices.*.price_list_id' => ['required_with:prices', 'integer', 'exists:price_lists,id'],
            'prices.*.price' => ['required_with:prices', 'numeric', 'min:0'],
            'prices.*.min_quantity' => ['nullable', 'integer', 'min:1'],
            'prices.*.is_deleted' => ['sometimes', 'boolean'],
        ];
    }
}
