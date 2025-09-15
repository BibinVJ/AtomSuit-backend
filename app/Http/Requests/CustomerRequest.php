<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:customers,name,'.$this->route('customer')?->id,
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:customers,email,'.$this->route('customer')?->id,
                'required_without:phone',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:customers,phone,'.$this->route('customer')?->id,
                'required_without:email',
            ],
            'address' => 'nullable|string|max:500',
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
