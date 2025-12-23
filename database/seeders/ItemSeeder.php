<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tablet = Category::where('name', 'Tablet')->first();
        $syrup = Category::where('name', 'Syrup')->first();
        $ointment = Category::where('name', 'Ointment')->first();

        $mg = Unit::where('code', 'mg')->first();
        $ml = Unit::where('code', 'ml')->first();
        $pcs = Unit::where('code', 'pcs')->first();

        $items = [
            ['name' => 'Paracetamol 500mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used for fever and pain', 'selling_price' => 25.00],
            ['name' => 'Cetrizine 10mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used for allergies', 'selling_price' => 15.00],
            ['name' => 'Vitamin C Chewable', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Immunity booster', 'selling_price' => 45.00],
            ['name' => 'Amoxicillin 250mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Antibiotic', 'selling_price' => 60.00],
            ['name' => 'Ibuprofen 400mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Anti-inflammatory painkiller', 'selling_price' => 30.00],

            ['name' => 'Benadryl Cough Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Cough relief', 'selling_price' => 95.00],
            ['name' => 'Lacto Calamine Lotion', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'For oily skin', 'selling_price' => 120.00],
            ['name' => 'Volini Gel', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'Pain relief gel', 'selling_price' => 90.00],
            ['name' => 'ORS Powder', 'category_id' => $tablet->id, 'unit_id' => $pcs->id, 'description' => 'Oral rehydration salts', 'selling_price' => 12.00],
            ['name' => 'Digene Antacid Liquid', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'For acidity and gas', 'selling_price' => 85.00],

            ['name' => 'Azithromycin 500mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Antibiotic for bacterial infections', 'selling_price' => 150.00],
            ['name' => 'Montelukast 10mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used to treat allergies and asthma', 'selling_price' => 110.00],
            ['name' => 'Multivitamin Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Nutritional supplement for kids', 'selling_price' => 140.00],
            ['name' => 'Calpol 250mg', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Pain and fever relief in children', 'selling_price' => 55.00],
            ['name' => 'Burnol Cream', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'First aid for burns', 'selling_price' => 45.00],
            ['name' => 'Neosporin Ointment', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'Antibiotic wound care ointment', 'selling_price' => 70.00],
            ['name' => 'Zincovit Tablets', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Multivitamin and mineral supplement', 'selling_price' => 120.00],
            ['name' => 'Electral Powder Sachet', 'category_id' => $tablet->id, 'unit_id' => $pcs->id, 'description' => 'Rehydration salt for dehydration', 'selling_price' => 18.00],
            ['name' => 'Cetaphil Moisturizing Lotion', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'For dry and sensitive skin', 'selling_price' => 250.00],
            ['name' => 'Ambroxol Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Mucolytic for cough with phlegm', 'selling_price' => 100.00],
        ];

        $salesAccount = \App\Models\ChartOfAccount::where('code', '4001')->first();
        $cogsAccount = \App\Models\ChartOfAccount::where('code', '5001')->first();
        $inventoryAccount = \App\Models\ChartOfAccount::where('code', '1004')->first();
        $inventoryAdjAccount = \App\Models\ChartOfAccount::where('code', '6003')->first();
        $purchaseAccount = \App\Models\ChartOfAccount::where('code', '5001')->first(); // Using COGS for purchase default for now

        $defaults = [
            'sales_account_id' => $salesAccount?->id,
            'cogs_account_id' => $cogsAccount?->id,
            'inventory_account_id' => $inventoryAccount?->id,
            'inventory_adjustment_account_id' => $inventoryAdjAccount?->id,
            'purchase_account_id' => $purchaseAccount?->id,
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(
                ['name' => $item['name']],
                array_merge([
                    'sku' => (string) mt_rand(10000000, 99999999),
                    'category_id' => $item['category_id'],
                    'unit_id' => $item['unit_id'],
                    'description' => $item['description'],
                    'selling_price' => $item['selling_price'],
                ], $defaults)
            );
        }
    }
}
