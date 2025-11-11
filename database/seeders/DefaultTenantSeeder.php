<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Services\TenantService;
use Illuminate\Database\Seeder;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('name', 'Lifetime')->first();
        
        if (!$plan) {
            $this->command->error('Lifetime plan not found. Please run PlanSeeder first.');
            return;
        }

        // Check if tenant already exists
        if (Tenant::where('email', 'company@example.com')->exists()) {
            $this->command->info('Default tenant already exists. Skipping...');
            return;
        }

        // Use TenantService to properly create tenant with domain and database
        $tenantService = app(TenantService::class);
        
        try {
            $tenant = $tenantService->create([
                'name' => 'company',
                'email' => 'company@example.com',
                'password' => 'Example@123',
                'plan_id' => $plan->id,
                'domain_name' => 'company',
                'load_sample_data' => true,
            ]);

            // Update email_verified_at since this is a seed tenant
            $tenant->email_verified_at = now();
            $tenant->save();

            $this->command->info('✓ Default tenant created successfully!');
            $this->command->info('  Email: company@example.com');
            $this->command->info('  Password: Example@123');
            $this->command->info('  Domain: ' . $tenant->domains->first()?->domain);
            
        } catch (\Exception $e) {
            $this->command->error('Failed to create default tenant: ' . $e->getMessage());
        }
    }
}
