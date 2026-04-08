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
            ['name' => 'Piece', 'code' => 'pcs', 'description' => 'General piece unit'],
            ['name' => 'Bottle', 'code' => 'btl', 'description' => 'Bottle unit for liquids'],
            ['name' => 'Strip', 'code' => 'strp', 'description' => 'Strip of tablets/capsules'],
            ['name' => 'Tube', 'code' => 'tb', 'description' => 'Tube for ointments/creams'],
            ['name' => 'Sachet', 'code' => 'sch', 'description' => 'Sachet for powders'],
            ['name' => 'Box', 'code' => 'box', 'description' => 'Box containing multiple units'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
