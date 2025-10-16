<?php

namespace App\Console\Commands;

use App\Enums\TenantStatusEnum;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TenantCreate extends Command
{
    protected $signature = 'tenant:create 
                            {name : Tenant name}
                            {email : Tenant email}
                            {domain : Tenant domain}
                            {plan_id : Plan ID}
                            {--load-sample-data : Load sample data}';

    protected $description = 'Create a new tenant';

    public function handle(): int
    {
        $plan = Plan::find($this->argument('plan_id'));
        if (!$plan) {
            $this->error("Plan with ID {$this->argument('plan_id')} does not exist.");
            return Command::FAILURE;
        }

        $tenant = Tenant::create([
            'name'             => $this->argument('name'),
            'email'            => $this->argument('email'),
            'status'           => TenantStatusEnum::ACTIVE->value,
            'trial_ends_at'    => null,
            'grace_period_ends_at' => null,
            'email_verified_at' => now(),
            'password'         => Hash::make('Example@123'), // secure hashing
            'load_sample_data' => $this->option('load-sample-data'),
            'domain_name'      => $this->argument('domain'),
            'plan_id'          => $plan->id,
        ]);

        $this->info("Tenant '{$tenant->name}' created successfully with domain '{$this->argument('domain')}'");

        return Command::SUCCESS;
    }
}
