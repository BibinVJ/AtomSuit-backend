<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\TaxGroup;
use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get GL Accounts (using common codes if available, or fallback)
        $salesAccount = ChartOfAccount::where('code', '2001')->first() ?? ChartOfAccount::first(); // Liability
        $purchaseAccount = ChartOfAccount::where('code', '1005')->first() ?? ChartOfAccount::first(); // Asset

        // 2. Create Tax Rates
        $cgst9 = TaxRate::firstOrCreate(
            ['name' => 'CGST 9%'],
            [
                'rate' => 9.00,
                'type' => 'percentage',
                'sales_account_id' => $salesAccount?->id,
                'purchase_account_id' => $purchaseAccount?->id,
            ]
        );

        $sgst9 = TaxRate::firstOrCreate(
            ['name' => 'SGST 9%'],
            [
                'rate' => 9.00,
                'type' => 'percentage',
                'sales_account_id' => $salesAccount?->id,
                'purchase_account_id' => $purchaseAccount?->id,
            ]
        );

        $igst18 = TaxRate::firstOrCreate(
            ['name' => 'IGST 18%'],
            [
                'rate' => 18.00,
                'type' => 'percentage',
                'sales_account_id' => $salesAccount?->id,
                'purchase_account_id' => $purchaseAccount?->id,
            ]
        );

        $exemptRate = TaxRate::firstOrCreate(
            ['name' => 'Zero Rate'],
            [
                'rate' => 0.00,
                'type' => 'percentage',
                'sales_account_id' => $salesAccount?->id,
                'purchase_account_id' => $purchaseAccount?->id,
            ]
        );

        // 3. Create Tax Groups and attach Rates
        $gst18Group = TaxGroup::firstOrCreate(['name' => 'GST 18%']);
        if ($gst18Group->taxRates()->count() === 0) {
            $gst18Group->taxRates()->attach([$cgst9->id, $sgst9->id]);
        }

        $igst18Group = TaxGroup::firstOrCreate(['name' => 'IGST 18%']);
        if ($igst18Group->taxRates()->count() === 0) {
            $igst18Group->taxRates()->attach([$igst18->id]);
        }

        $exemptGroup = TaxGroup::firstOrCreate(['name' => 'Exempt']);
        if ($exemptGroup->taxRates()->count() === 0) {
            $exemptGroup->taxRates()->attach([$exemptRate->id]);
        }
    }
}
