<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();

        // Fetch Default Accounts
        $salesAccount = \App\Models\ChartOfAccount::where('code', '4001')->first();
        $salesDiscountAccount = \App\Models\ChartOfAccount::where('code', '4002')->first();
        $salesReturnAccount = \App\Models\ChartOfAccount::where('code', '4003')->first();
        $receivablesAccount = \App\Models\ChartOfAccount::where('code', '1003')->first();

        $defaults = [
            'sales_account_id' => $salesAccount?->id,
            'sales_discount_account_id' => $salesDiscountAccount?->id,
            'sales_return_account_id' => $salesReturnAccount?->id,
            'receivables_account_id' => $receivablesAccount?->id,
        ];

        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '1234567890',
                'currency_id' => $inr?->id,
                'billing_address_line_1' => '123 Main St',
                'billing_address_line_2' => 'Suite 100',
                'billing_city' => 'Mumbai',
                'billing_state' => 'Maharashtra',
                'billing_country' => 'India',
                'billing_zip_code' => '400001',
                'shipping_address_line_1' => '123 Main St',
                'shipping_address_line_2' => 'Warehouse A',
                'shipping_city' => 'Mumbai',
                'shipping_state' => 'Maharashtra',
                'shipping_country' => 'India',
                'shipping_zip_code' => '400001',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '0987654321',
                'currency_id' => $usd?->id,
                'billing_address_line_1' => '456 Elm St',
                'billing_address_line_2' => 'Apt 4B',
                'billing_city' => 'New York',
                'billing_state' => 'New York',
                'billing_country' => 'United States',
                'billing_zip_code' => '10001',
                'shipping_address_line_1' => '456 Elm St',
                'shipping_address_line_2' => 'Receiving Dock',
                'shipping_city' => 'New York',
                'shipping_state' => 'New York',
                'shipping_country' => 'United States',
                'shipping_zip_code' => '10001',
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                array_merge($customer, $defaults)
            );
        }
    }
}
