<?php

namespace App\Http\Requests;

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
                function ($attribute, $value, $fail) use ($id) {
                    $query = \Illuminate\Support\Facades\DB::table('exchange_rates')
                        ->where('base_currency_id', $value)
                        ->where('target_currency_id', $this->target_currency_id)
                        ->where('effective_date', $this->effective_date ?? now()->toDateString());

                    if ($id) {
                        $query->where('id', '!=', $id);
                    }

                    $record = $query->first();

                    if ($record) {
                        if (isset($record->deleted_at) && $record->deleted_at !== null) {
                            $fail('The exchange rate for this currency pair and date exists in the trash. Please restore it.');
                        } else {
                            $fail('The exchange rate for this currency pair and date has already been taken.');
                        }
                    }
                },
            ],
            'target_currency_id' => 'required|exists:currencies,id|different:base_currency_id',
            'rate' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
        ];
    }
}
