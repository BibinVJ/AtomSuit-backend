<?php

namespace App\Http\Requests;

use App\Enums\SocialPlatformEnum;
use Illuminate\Foundation\Http\FormRequest;

class UserSocialLinksRequest extends FormRequest
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
        $rules = [];
        foreach (SocialPlatformEnum::values() as $platform) {
            $rules[$platform] = ['nullable', 'url', 'max:255'];
        }

        return $rules;
    }
}
