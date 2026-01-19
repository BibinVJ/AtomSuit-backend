<?php

namespace App\Http\Requests;

use App\Rules\UniqueInTrash;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountGroupRequest extends FormRequest
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
            'code' => ['nullable', 'string', 'max:50', new UniqueInTrash('account_groups', 'code', $this->route('account_group')?->id)],
            'account_type_id' => 'required|exists:account_types,id',
            'parent_id' => ['nullable', Rule::exists('account_groups', 'id')],
            'description' => 'nullable|string',
        ];
    }
}
