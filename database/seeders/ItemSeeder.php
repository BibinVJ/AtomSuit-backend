<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            ['name' => 'Paracetamol 500mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used for fever and pain'],
            ['name' => 'Cetrizine 10mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used for allergies'],
            ['name' => 'Vitamin C Chewable', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Immunity booster'],
            ['name' => 'Amoxicillin 250mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Antibiotic'],
            ['name' => 'Ibuprofen 400mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Anti-inflammatory painkiller'],

            ['name' => 'Benadryl Cough Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Cough relief'],
            ['name' => 'Lacto Calamine Lotion', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'For oily skin'],
            ['name' => 'Volini Gel', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'Pain relief gel'],
            ['name' => 'ORS Powder', 'category_id' => $tablet->id, 'unit_id' => $pcs->id, 'description' => 'Oral rehydration salts'],
            ['name' => 'Digene Antacid Liquid', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'For acidity and gas'],

            ['name' => 'Azithromycin 500mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Antibiotic for bacterial infections'],
            ['name' => 'Montelukast 10mg', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Used to treat allergies and asthma'],
            ['name' => 'Multivitamin Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Nutritional supplement for kids'],
            ['name' => 'Calpol 250mg', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Pain and fever relief in children'],
            ['name' => 'Burnol Cream', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'First aid for burns'],
            ['name' => 'Neosporin Ointment', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'Antibiotic wound care ointment'],
            ['name' => 'Zincovit Tablets', 'category_id' => $tablet->id, 'unit_id' => $mg->id, 'description' => 'Multivitamin and mineral supplement'],
            ['name' => 'Electral Powder Sachet', 'category_id' => $tablet->id, 'unit_id' => $pcs->id, 'description' => 'Rehydration salt for dehydration'],
            ['name' => 'Cetaphil Moisturizing Lotion', 'category_id' => $ointment->id, 'unit_id' => $ml->id, 'description' => 'For dry and sensitive skin'],
            ['name' => 'Ambroxol Syrup', 'category_id' => $syrup->id, 'unit_id' => $ml->id, 'description' => 'Mucolytic for cough with phlegm'],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(
                ['name' => $item['name']],
                [
                    'sku' => Str::uuid(),
                    'category_id' => $item['category_id'],
                    'unit_id' => $item['unit_id'],
                    'description' => $item['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}
