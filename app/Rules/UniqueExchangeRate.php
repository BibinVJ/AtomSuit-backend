<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueExchangeRate implements ValidationRule
{
    public function __construct(
        protected mixed $targetCurrencyId,
        protected mixed $effectiveDate,
        protected mixed $ignoreId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table('exchange_rates')
            ->where('base_currency_id', $value)
            ->where('target_currency_id', $this->targetCurrencyId)
            ->where('effective_date', $this->effectiveDate);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $record = $query->first();

        if ($record) {
            if (isset($record->deleted_at) && $record->deleted_at !== null) {
                $fail('The exchange rate for this currency pair and date exists in the trash. Please restore it.');
            } else {
                $fail('The exchange rate for this currency pair and date has already been taken.');
            }
        }
    }
}
