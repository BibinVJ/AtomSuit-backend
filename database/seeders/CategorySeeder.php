<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\TaxGroup;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesAccount = ChartOfAccount::where('name', 'Sales Revenue')->first();
        $cogsAccount = ChartOfAccount::where('name', 'Cost of Goods Sold')->first();
        $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
        $adjustmentAccount = ChartOfAccount::where('name', 'Inventory Adjustment')->first();

        $taxGroup = TaxGroup::where('name', 'GST 18%')->first();

        $categories = [
            [
                'name' => 'Tablet',
                'description' => 'Solid dosage form',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
                'tax_group_id' => $taxGroup?->id,
            ],
            [
                'name' => 'Syrup',
                'description' => 'Liquid medicine',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
                'tax_group_id' => $taxGroup?->id,
            ],
            [
                'name' => 'Ointment',
                'description' => 'Topical treatment',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
                'tax_group_id' => $taxGroup?->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
