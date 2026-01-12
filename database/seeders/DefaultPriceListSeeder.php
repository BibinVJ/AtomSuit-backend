<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\PriceList;
use Illuminate\Database\Seeder;

class DefaultPriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencyId = \App\Models\Setting::where('key', 'currency')->value('value');
        $currency = Currency::find($currencyId);

        if (! $currency) {
            $currency = Currency::where('code', 'INR')->first() ?? Currency::first();
        }

        if (! $currency) {
            return;
        }

        // 1. Create Standard Sales Price List
        PriceList::firstOrCreate(
            ['code' => 'PL-SALES-STD-'.$currency->code],
            [
                'name' => 'Standard Sales Price List',
                'currency_id' => $currency->id,
                'type' => 'sales',
                'is_tax_inclusive' => true,
                'description' => 'Default price list for retail sales.',
            ]
        );

        // 2. Create Standard Purchase Price List
        PriceList::firstOrCreate(
            ['code' => 'PL-PURCHASE-STD-'.$currency->code],
            [
                'name' => 'Standard Purchase Price List',
                'currency_id' => $currency->id,
                'type' => 'purchase',
                'is_tax_inclusive' => false,
                'description' => 'Default price list for purchasing.',
            ]
        );
    }
}
