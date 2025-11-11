<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CentralDatabaseSeeder extends Seeder
{
    /**
     * Run the central database seeds.
     */
    public function run(): void
    {
        $this->call([
            CentralRolesAndPermissionsSeeder::class,
            SuperAdminSeeder::class,
            PlanSeeder::class,
            SettingSeeder::class,
        ]);
    }
}