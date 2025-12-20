<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tablet', 'description' => 'Solid dosage form'],
            ['name' => 'Syrup', 'description' => 'Liquid medicine'],
            ['name' => 'Ointment', 'description' => 'Topical treatment'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
