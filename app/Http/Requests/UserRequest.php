<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $user = $this->route('user');

        $isUpdate = (bool) $user;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user),
            ],
            'password' => $isUpdate
                ? 'prohibited'
                : 'required|string|min:6|max:255',
            'role_id' => 'required|exists:roles,id',
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user),
            ],
            'is_active' => 'boolean',
        ];
    }
}
