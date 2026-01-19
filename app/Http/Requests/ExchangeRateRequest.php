<?php

namespace App\Http\Requests;

use App\Rules\UniqueExchangeRate;
use Illuminate\Foundation\Http\FormRequest;

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
                new UniqueExchangeRate(
                    $this->target_currency_id,
                    $this->effective_date ?? now()->toDateString(),
                    $id
                ),
            ],
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ];
    }
}
