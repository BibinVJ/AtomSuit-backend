<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class TenantDefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,

        ]);

        // $client = new ClientRepository();

        // $client->createPasswordGrantClient(null, 'Default password grant client', 'http://your.redirect.path');
        // $client->createPersonalAccessClient(null, 'Default personal access client', 'http://your.redirect.path');
    }
}
