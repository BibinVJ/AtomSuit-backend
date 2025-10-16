<?php

namespace App\Http\Requests;

use App\Enums\PlanIntervalEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlanRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:plans,name,' . $this->route('plan')?->id,
            'price' => 'required|numeric|min:0',
            'interval' => ['required', new Enum(PlanIntervalEnum::class)],
            'interval_count' => 'required|integer|min:1',
            'is_trial_plan' => 'boolean',
            'trial_duration_in_days' => 'required_if:is_trial_plan,true|integer|min:1',
            'is_expired_user_plan' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
