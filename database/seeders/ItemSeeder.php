<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
