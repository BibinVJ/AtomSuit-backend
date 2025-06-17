<?php

namespace App\Http\Requests;

use App\Enums\BoothStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BoothRequest extends FormRequest
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
            'unique_id'   => 'required|string|unique:booths,unique_id,' . optional($this->route('booth'))->id,
            'name'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'size'        => 'nullable|string',
            'price'       => 'nullable|numeric|min:0',
            'status'      => ['nullable', new Enum(BoothStatus::class)],
            'is_active'   => 'boolean',
        ];  
    }
}
