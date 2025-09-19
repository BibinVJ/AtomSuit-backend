<?php

namespace App\Console\Commands;

use App\Enums\TenantStatusEnum;
use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create {name} {email} {domain} {plan_id}
                                {--loadSampleData=true : Load sample data}';

    protected $description = 'Create a new tenant';

    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $email = $this->argument('email');
        $plan_id = $this->argument('plan_id');

        $tenant = Tenant::create([
            'name' => $name,
            'email' => $email,
            'status' => TenantStatusEnum::ACTIVE->value,
            'trial_ends_at' => null,
            'grace_period_ends_at' => null,

            'email_verified_at' => now(),
            'password' => 'Example@123',
            'load_sample_data' => $this->option('loadSampleData'),
            'domain_name' => $domain,
            'plan_id' => $plan_id,
        ]);

        $this->info("Tenant '{$name}' created successfully with domain '{$domain}'");

        return Command::SUCCESS;
    }
}