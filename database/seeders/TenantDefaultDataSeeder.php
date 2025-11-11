<?php

namespace Database\Seeders;

use App\Services\DashboardService;
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
            DashboardService::class,

        ]);
    }
}
