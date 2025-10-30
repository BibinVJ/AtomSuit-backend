<?php

namespace App\Http\Requests\Auth;

use App\Enums\RolesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class RegisterRequest extends FormRequest
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
            'email' => 'required|email|unique:tenants,email',
            'password' => 'required|string|min:6',
            'domain_name' => 'required|unique:domains,domain',
            'load_sample_data' => 'required|boolean',
            'selected_plan_id' => 'required|exists:plans,id',
        ];
    }
}
