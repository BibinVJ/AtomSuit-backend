<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\PriceList;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class WalkInCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencyId = Setting::where('key', 'currency')->value('value');
        $currency = Currency::find($currencyId);

        if (! $currency) {
            $currency = Currency::first();
        }

        if (! $currency) {
            return;
        }

        // Fetch standard sales price list for this currency
        $standardSalesList = PriceList::where('type', 'sales')
            ->where('currency_id', $currency->id)
            ->first();

        $salesAccount = ChartOfAccount::where('code', '4001')->first();
        $salesDiscountAccount = ChartOfAccount::where('code', '4002')->first();
        $salesReturnAccount = ChartOfAccount::where('code', '4003')->first();
        $receivablesAccount = ChartOfAccount::where('code', '1003')->first();

        // Ensure we handle missing accounts gracefully
        $defaults = [
            'sales_account_id' => $salesAccount?->id,
            'sales_discount_account_id' => $salesDiscountAccount?->id,
            'sales_return_account_id' => $salesReturnAccount?->id,
            'receivables_account_id' => $receivablesAccount?->id,
            'price_list_id' => $standardSalesList?->id,
            'currency_id' => $currency->id,
        ];

        Customer::firstOrCreate(
            ['phone' => '0000000000'], // Use a dummy phone identifier
            array_merge([
                'name' => 'Walk-in Customer',
                'email' => null,
                'billing_address_line_1' => 'Local',
                'billing_city' => 'Local',
                'billing_country' => 'India',
            ], $defaults)
        );
    }
}
