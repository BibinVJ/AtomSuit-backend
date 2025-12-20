<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('exchange_rate') ? $this->route('exchange_rate')->id : null;

        return [
            'base_currency_id' => [
                'required',
                'exists:currencies,id',
                Rule::unique('exchange_rates')->where(function ($query) {
                    return $query->where('target_currency_id', $this->target_currency_id)
                        ->where('effective_date', $this->effective_date ?? now()->toDateString());
                })->ignore($id),
            ],
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ];
    }
}
