<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();

        $vendors = [
            [
                'name' => 'Vendor One',
                'email' => 'vendor.one@example.com',
                'phone' => '1234567890',
                'currency_id' => $inr?->id,
                'billing_address_line_1' => '123 Vendor St',
                'billing_address_line_2' => 'Building 1',
                'billing_city' => 'Bangalore',
                'billing_state' => 'Karnataka',
                'billing_country' => 'India',
                'billing_zip_code' => '560001',
                'shipping_address_line_1' => '123 Vendor St',
                'shipping_address_line_2' => 'Dispatch Unit',
                'shipping_city' => 'Bangalore',
                'shipping_state' => 'Karnataka',
                'shipping_country' => 'India',
                'shipping_zip_code' => '560001',
            ],
            [
                'name' => 'Vendor Two',
                'email' => 'vendor.two@example.com',
                'phone' => '9876543210',
                'currency_id' => $usd?->id,
                'billing_address_line_1' => '456 Vendor Ave',
                'billing_address_line_2' => 'Suite 505',
                'billing_city' => 'San Francisco',
                'billing_state' => 'California',
                'billing_country' => 'United States',
                'billing_zip_code' => '94107',
                'shipping_address_line_1' => '456 Vendor Ave',
                'shipping_address_line_2' => 'Loading Bay',
                'shipping_city' => 'San Francisco',
                'shipping_state' => 'California',
                'shipping_country' => 'United States',
                'shipping_zip_code' => '94107',
            ],
        ];

        // Fetch Default Accounts
        $payablesAccount = \App\Models\ChartOfAccount::where('code', '2001')->first();
        $purchaseAccount = \App\Models\ChartOfAccount::where('code', '5001')->first();
        $purchaseDiscountAccount = \App\Models\ChartOfAccount::where('code', '5002')->first();
        $purchaseReturnAccount = \App\Models\ChartOfAccount::where('code', '5003')->first();

        $defaults = [
            'payables_account_id' => $payablesAccount?->id,
            'purchase_account_id' => $purchaseAccount?->id,
            'purchase_discount_account_id' => $purchaseDiscountAccount?->id,
            'purchase_return_account_id' => $purchaseReturnAccount?->id,
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(
                ['email' => $vendor['email']],
                array_merge($vendor, $defaults)
            );
        }
    }
}
