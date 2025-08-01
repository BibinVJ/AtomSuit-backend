<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardLayoutRequest extends FormRequest
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
            'layouts' => ['required', 'array'],
            'layouts.*.card_id' => ['required', 'string'],
            'layouts.*.area' => ['nullable', 'string'],
            'layouts.*.x' => ['nullable', 'numeric'],
            'layouts.*.y' => ['nullable', 'numeric'],
            'layouts.*.rotation' => ['nullable', 'numeric'],
            'layouts.*.width' => ['nullable', 'numeric'],
            'layouts.*.height' => ['nullable', 'numeric'],
            'layouts.*.col_span' => ['nullable', 'integer'],
            'layouts.*.draggable' => ['nullable', 'boolean'],
            'layouts.*.visible' => ['required', 'boolean'],
            'layouts.*.config' => ['nullable', 'array'],
        ];
    }
}
