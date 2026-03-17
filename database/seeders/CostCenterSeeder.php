<?php

namespace Database\Seeders;

use App\Enums\CostCenterType;
use App\Models\CostCenter;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainWarehouse = Warehouse::where('code', 'WH-MAIN')->first();
        $regionalWarehouse = Warehouse::where('code', 'WH-REG')->first();

        // 1. Create Brand (Top Level)
        $brand = CostCenter::updateOrCreate(
            ['code' => 'BRAND-ATOM'],
            [
                'name' => 'AtomSuit Global',
                'type' => CostCenterType::BRAND,
                'warehouse_id' => $mainWarehouse?->id,
            ]
        );

        // 2. Create Branches under Brand
        $mainBranch = CostCenter::updateOrCreate(
            ['code' => 'BR-MAIN'],
            [
                'name' => 'Main Branch',
                'type' => CostCenterType::BRANCH,
                'parent_id' => $brand->id,
                'warehouse_id' => $mainWarehouse?->id,
            ]
        );

        $eastBranch = CostCenter::updateOrCreate(
            ['code' => 'BR-EAST'],
            [
                'name' => 'East Coast Branch',
                'type' => CostCenterType::BRANCH,
                'parent_id' => $brand->id,
                'warehouse_id' => $regionalWarehouse?->id,
            ]
        );

        // 3. Create Department under Main Branch
        CostCenter::updateOrCreate(
            ['code' => 'DEPT-SALES'],
            [
                'name' => 'Sales Department',
                'type' => CostCenterType::DEPARTMENT,
                'parent_id' => $mainBranch->id,
                'warehouse_id' => $mainWarehouse?->id,
            ]
        );

        // Optional: Independent Project
        CostCenter::updateOrCreate(
            ['code' => 'PROJ-SUMMER-24'],
            [
                'name' => 'Summer Campaign 2024',
                'type' => CostCenterType::PROJECT,
                'warehouse_id' => $mainWarehouse?->id,
                // Projects might be top-level or under a department/branch depending on policy
            ]
        );
    }
}
