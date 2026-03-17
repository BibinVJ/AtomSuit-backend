<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This is a set of sample data for tenants to help them get started quickly.
     * This seeder runs automalically if the tenant opts in for sample data during onboarding.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            PriceListSeeder::class,
            UsersSeeder::class,
            TaxSeeder::class,
            CategorySeeder::class,
            UnitSeeder::class,
            ItemSeeder::class,
            WarehouseSeeder::class,
            CostCenterSeeder::class,

            VendorSeeder::class,
            CustomerSeeder::class,

            // PurchaseSeeder::class,
            // SaleSeeder::class,
        ]);
    }
}
