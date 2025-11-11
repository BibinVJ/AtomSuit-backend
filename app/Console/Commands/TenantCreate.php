<?php

namespace App\Console\Commands;

use App\Services\TenantService;
use Illuminate\Console\Command;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create 
                            {name : Tenant name}
                            {email : Tenant email}
                            {domain : Tenant domain}
                            {plan_id : Plan ID}
                            {--password=Example@123 : Tenant password}
                            {--load-sample-data : Load sample data}';

    protected $description = 'Create a new tenant with offline payment';

    public function __construct(protected TenantService $tenantService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $tenant = $this->tenantService->create([
                'name' => $this->argument('name'),
                'email' => $this->argument('email'),
                'password' => $this->option('password'),
                'domain_name' => $this->argument('domain'),
                'plan_id' => $this->argument('plan_id'),
                'load_sample_data' => $this->option('load-sample-data'),
            ]);

            $this->info("Tenant '{$tenant->name}' created successfully with domain '{$this->argument('domain')}'");
            $this->info("Plan: {$tenant->plan->name}");
            
            if ($tenant->currentSubscription) {
                $this->info("Subscription created: {$tenant->currentSubscription->stripe_id}");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create tenant: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
