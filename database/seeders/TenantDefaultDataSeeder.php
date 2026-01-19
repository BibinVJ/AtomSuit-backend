<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant database.
     *
     * This is a set of data that is required for every tenant to function properly.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DefaultCurrencySeeder::class,
            AccountTypeSeeder::class,
            AccountGroupSeeder::class,
            ChartOfAccountSeeder::class,
            DashboardCardSeeder::class,
            SettingSeeder::class,

            DefaultPriceListSeeder::class,
            WalkInCustomerSeeder::class,
        ]);
    }
}
