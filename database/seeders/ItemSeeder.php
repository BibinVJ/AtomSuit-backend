<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChartOfAccount;
use App\Models\Currency;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\PriceList;
use App\Models\TaxGroup;
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

        $strp = Unit::where('code', 'strp')->first(); // tablet strips
        $btl = Unit::where('code', 'btl')->first(); // syrups / lotions
        $tb = Unit::where('code', 'tb')->first(); // ointments / gels
        $sch = Unit::where('code', 'sch')->first(); // sachets
        $box = Unit::where('code', 'box')->first(); // boxes

        $items = [
            ['name' => 'Paracetamol 500mg (10 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Used for fever and pain', 'selling_price' => 25.00],
            ['name' => 'Cetrizine 10mg (10 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Used for allergies', 'selling_price' => 15.00],
            ['name' => 'Vitamin C Chewable (30 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $btl?->id, 'description' => 'Immunity booster', 'selling_price' => 45.00],
            ['name' => 'Amoxicillin 250mg (6 Capsules)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Antibiotic', 'selling_price' => 60.00],
            ['name' => 'Ibuprofen 400mg (10 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Anti-inflammatory painkiller', 'selling_price' => 30.00],

            ['name' => 'Benadryl Cough Syrup 100ml', 'category_id' => $syrup->id, 'unit_id' => $btl?->id, 'description' => 'Cough relief', 'selling_price' => 95.00],
            ['name' => 'Lacto Calamine Lotion 120ml', 'category_id' => $ointment->id, 'unit_id' => $btl?->id, 'description' => 'For oily skin', 'selling_price' => 120.00],
            ['name' => 'Volini Gel 30g', 'category_id' => $ointment->id, 'unit_id' => $tb?->id, 'description' => 'Pain relief gel', 'selling_price' => 90.00],
            ['name' => 'ORS Powder 21g', 'category_id' => $tablet->id, 'unit_id' => $sch?->id, 'description' => 'Oral rehydration salts', 'selling_price' => 12.00],
            ['name' => 'Digene Antacid Liquid 200ml', 'category_id' => $syrup->id, 'unit_id' => $btl?->id, 'description' => 'For acidity and gas', 'selling_price' => 85.00],

            ['name' => 'Azithromycin 500mg (3 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Antibiotic for bacterial infections', 'selling_price' => 150.00],
            ['name' => 'Montelukast 10mg (10 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Used to treat allergies and asthma', 'selling_price' => 110.00],
            ['name' => 'Multivitamin Syrup 200ml', 'category_id' => $syrup->id, 'unit_id' => $btl?->id, 'description' => 'Nutritional supplement for kids', 'selling_price' => 140.00],
            ['name' => 'Calpol 250mg Suspension 60ml', 'category_id' => $syrup->id, 'unit_id' => $btl?->id, 'description' => 'Pain and fever relief in children', 'selling_price' => 55.00],
            ['name' => 'Burnol Cream 20g', 'category_id' => $ointment->id, 'unit_id' => $tb?->id, 'description' => 'First aid for burns', 'selling_price' => 45.00],
            ['name' => 'Neosporin Ointment 15g', 'category_id' => $ointment->id, 'unit_id' => $tb?->id, 'description' => 'Antibiotic wound care ointment', 'selling_price' => 70.00],
            ['name' => 'Zincovit Tablets (15 Tablets)', 'category_id' => $tablet->id, 'unit_id' => $strp?->id, 'description' => 'Multivitamin and mineral supplement', 'selling_price' => 120.00],
            ['name' => 'Electral Powder Sachet 21g', 'category_id' => $tablet->id, 'unit_id' => $sch?->id, 'description' => 'Rehydration salt for dehydration', 'selling_price' => 18.00],
            ['name' => 'Cetaphil Moisturizing Lotion 250ml', 'category_id' => $ointment->id, 'unit_id' => $btl?->id, 'description' => 'For dry and sensitive skin', 'selling_price' => 250.00],
            ['name' => 'Ambroxol Syrup 250ml', 'category_id' => $syrup->id, 'unit_id' => $btl?->id, 'description' => 'Mucolytic for cough with phlegm', 'selling_price' => 100.00],
        ];

        $salesAccount = ChartOfAccount::where('code', '4001')->first();
        $cogsAccount = ChartOfAccount::where('code', '5001')->first();
        $inventoryAccount = ChartOfAccount::where('code', '1004')->first();
        $inventoryAdjAccount = ChartOfAccount::where('code', '6003')->first();

        $taxGroup = TaxGroup::where('name', 'GST 18%')->first();

        // Fetch all relevant lists
        $inr = Currency::where('code', 'INR')->first();
        $usd = Currency::where('code', 'USD')->first();

        // Lists should be created by DefaultPriceListSeeder (INR) and PriceListSeeder (USD)
        $salesListInr = PriceList::where('type', 'sales')->where('currency_id', $inr?->id)->first();
        $salesListUsd = PriceList::where('type', 'sales')->where('currency_id', $usd?->id)->first();

        $purchaseListInr = PriceList::where('type', 'purchase')->where('currency_id', $inr?->id)->first();
        $purchaseListUsd = PriceList::where('type', 'purchase')->where('currency_id', $usd?->id)->first();

        $defaults = [
            'sales_account_id' => $salesAccount?->id,
            'cogs_account_id' => $cogsAccount?->id,
            'inventory_account_id' => $inventoryAccount?->id,
            'inventory_adjustment_account_id' => $inventoryAdjAccount?->id,
            'tax_group_id' => $taxGroup?->id,
        ];

        foreach ($items as $index => $itemData) {
            // Remove selling_price from item creation data
            $sellingPrice = $itemData['selling_price'];
            unset($itemData['selling_price']);

            $item = Item::updateOrCreate(
                ['name' => $itemData['name']],
                array_merge([
                    'sku' => (string) mt_rand(10000000, 99999999),
                ], $itemData, $defaults)
            );

            // Selective Pricing: Only price even-indexed items (50% of items), or based on logic
            // Let's price items if index is divisible by 2 or 3, leaving some unpriced
            if ($index % 4 === 0) {
                continue; // Skip 25% of items to test 'No Price' scenario
            }

            // --- INR PRICING ---
            if ($salesListInr) {
                ItemPrice::updateOrCreate(
                    ['price_list_id' => $salesListInr->id, 'item_id' => $item->id, 'min_quantity' => 1],
                    ['price' => $sellingPrice]
                );
            }
            if ($purchaseListInr) {
                ItemPrice::updateOrCreate(
                    ['price_list_id' => $purchaseListInr->id, 'item_id' => $item->id, 'min_quantity' => 1],
                    ['price' => $sellingPrice * 0.70]
                );
            }

            // --- USD PRICING ---
            // Approx Exchange Rate: 1 USD = 83 INR
            $usdPrice = $sellingPrice / 83.0;

            if ($salesListUsd) {
                ItemPrice::updateOrCreate(
                    ['price_list_id' => $salesListUsd->id, 'item_id' => $item->id, 'min_quantity' => 1],
                    ['price' => $usdPrice]
                );
            }
            if ($purchaseListUsd) {
                ItemPrice::updateOrCreate(
                    ['price_list_id' => $purchaseListUsd->id, 'item_id' => $item->id, 'min_quantity' => 1],
                    ['price' => $usdPrice * 0.70]
                );
            }
        }
    }
}
