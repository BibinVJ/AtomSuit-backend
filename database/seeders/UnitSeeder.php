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
            ['name' => 'Milligram', 'code' => 'mg', 'description' => 'Weight unit'],
            ['name' => 'Millilitre', 'code' => 'ml', 'description' => 'Volume unit'],
            ['name' => 'Piece', 'code' => 'pcs', 'description' => 'General unit'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
