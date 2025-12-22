<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'code' => ['nullable', 'string', 'max:50', new \App\Rules\UniqueInTrash('account_groups', 'code', $this->route('account_group'))],
            'account_type_id' => 'required|exists:account_types,id',
            'parent_id' => ['nullable', \Illuminate\Validation\Rule::exists('account_groups', 'id')],
            'description' => 'nullable|string',
        ];
    }
}
