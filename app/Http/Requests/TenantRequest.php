<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TenantRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:255', new \App\Rules\UniqueInTrash('tenants', 'email')],
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|max:255',
            'plan_id' => 'required|exists:plans,id',
            'domain_name' => [
                'required',
                'string',
                'max:200',
                'regex:/^(?!-)[a-z0-9-]+(?<!-)$/i', // DNS-safe slug
            ],
            'load_sample_data' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'domain_name.regex' => 'The domain name may only contain letters, numbers, and hyphens, and cannot start or end with a hyphen.',
        ];
    }
}
