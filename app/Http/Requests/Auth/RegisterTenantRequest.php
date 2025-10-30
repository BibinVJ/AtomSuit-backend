<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6|max:255',
            'domain_name' => [
                'required','string','max:200','regex:/^(?!-)[a-z0-9-]+(?<!-)$/i'
            ],
            'load_sample_data' => 'sometimes|boolean',
            'selected_plan_id' => 'sometimes|integer|exists:plans,id',
        ];
    }

    public function messages(): array
    {
        return [
            'domain_name.regex' => 'The domain name may only contain letters, numbers, and hyphens, and cannot start or end with a hyphen.',
        ];
    }
}