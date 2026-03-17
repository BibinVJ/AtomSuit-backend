<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH-MAIN',
                'description' => 'Primary storage location for all inventory.',
                'address_line_1' => '123 Main St, Industrial Park',
                'city' => 'Metropolis',
                'country' => 'USA',
            ],
            [
                'name' => 'Regional Warehouse',
                'code' => 'WH-REG',
                'description' => 'Secondary storage for regional distribution.',
                'address_line_1' => '456 West Ave, Logistics Hub',
                'city' => 'Gotham',
                'country' => 'USA',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(
                ['name' => $warehouse['name']],
                $warehouse
            );
        }
    }
}
