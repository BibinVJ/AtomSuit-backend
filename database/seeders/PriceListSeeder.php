<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\PriceList;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usd = Currency::where('code', 'USD')->first();

        if ($usd) {
            PriceList::firstOrCreate(
                ['code' => 'PL-SALES-STD-'.$usd->code],
                [
                    'name' => 'Standard Sales Price List (USD)',
                    'currency_id' => $usd->id,
                    'type' => 'sales',
                    'is_tax_inclusive' => true,
                    'description' => 'Default price list for USD retail sales.',
                ]
            );

            PriceList::firstOrCreate(
                ['code' => 'PL-PURCHASE-STD-'.$usd->code],
                [
                    'name' => 'Standard Purchase Price List (USD)',
                    'currency_id' => $usd->id,
                    'type' => 'purchase',
                    'is_tax_inclusive' => false,
                    'description' => 'Default price list for USD purchasing.',
                ]
            );
        }
    }
}
