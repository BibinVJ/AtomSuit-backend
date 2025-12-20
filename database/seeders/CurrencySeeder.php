<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'INR',
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'is_default' => true,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'precision' => 2,
                'symbol_position' => 'before',
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'is_default' => false,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'precision' => 2,
                'symbol_position' => 'before',
            ],
            [
                'code' => 'QAR',
                'name' => 'Qatari Rial',
                'symbol' => '﷼',
                'is_default' => false,
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'precision' => 2,
                'symbol_position' => 'after',
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
