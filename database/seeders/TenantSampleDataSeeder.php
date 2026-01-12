<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
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

            VendorSeeder::class,
            CustomerSeeder::class,

            PurchaseSeeder::class,
            SaleSeeder::class,
        ]);
    }
}
