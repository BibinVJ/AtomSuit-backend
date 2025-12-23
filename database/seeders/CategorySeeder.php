<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
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

        $categories = [
            [
                'name' => 'Tablet',
                'description' => 'Solid dosage form',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
            ],
            [
                'name' => 'Syrup',
                'description' => 'Liquid medicine',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
            ],
            [
                'name' => 'Ointment',
                'description' => 'Topical treatment',
                'sales_account_id' => $salesAccount?->id,
                'cogs_account_id' => $cogsAccount?->id,
                'inventory_account_id' => $inventoryAccount?->id,
                'inventory_adjustment_account_id' => $adjustmentAccount?->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
