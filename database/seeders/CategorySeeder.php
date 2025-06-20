<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Tablet', 'description' => 'Solid dosage form', 'is_active' => true],
            ['name' => 'Syrup', 'description' => 'Liquid medicine', 'is_active' => true],
            ['name' => 'Ointment', 'description' => 'Topical treatment', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
