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
                'nullable',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user),
                'required_without:phone',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user),
                'required_without:email',
            ],
            'password' => $isUpdate
                ? 'prohibited'
                : 'required|string|min:6|max:255',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Either an email or phone number is required.',
            'phone.required_without' => 'Either a phone number or email is required.',
        ];
    }
}
