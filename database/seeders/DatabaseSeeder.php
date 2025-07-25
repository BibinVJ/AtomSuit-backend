<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DefaultAdminUserSeeder::class,
            UsersSeeder::class,

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
