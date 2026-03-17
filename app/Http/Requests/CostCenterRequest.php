<?php

namespace App\Http\Requests;

use App\Enums\CostCenterType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CostCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('cost_centers')->ignore($this->cost_center)],
            'type' => ['required', Rule::enum(CostCenterType::class)],
            'parent_id' => ['nullable', 'exists:cost_centers,id'],
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
        ];
    }
}
