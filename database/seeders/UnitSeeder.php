<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Milligram', 'code' => 'mg', 'description' => 'Weight unit', 'is_active' => true],
            ['name' => 'Millilitre', 'code' => 'ml', 'description' => 'Volume unit', 'is_active' => true],
            ['name' => 'Piece', 'code' => 'pcs', 'description' => 'General unit', 'is_active' => true],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
