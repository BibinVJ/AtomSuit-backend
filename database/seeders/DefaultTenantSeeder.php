<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('name', 'Lifetime')->first();
        $company = [
            'name' => 'company',
            'email' => 'company@example.com',
            'password' => 'Example@123',
            'plan_id' => $plan->id,
            'email_verified_at' => now(),
            'load_sample_data' => true,
            'domain_name' => 'company',
        ];

        Tenant::firstOrCreate($company);
    }
}
