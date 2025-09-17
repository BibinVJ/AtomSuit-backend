<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     * This seeder is used for tenant-specific data.
     */
    public function run(): void
    {
        // This will be run in tenant context
        $this->call([
            TenantDefaultDataSeeder::class,
        ]);
    }
}
