<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CurrencySeeder::class,
            ChartOfAccountSeeder::class,
            DashboardCardSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
