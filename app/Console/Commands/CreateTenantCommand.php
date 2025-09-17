<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class CreateTenantCommand extends Command
{
    protected $signature = 'tenant:create {name} {domain} {email}';

    protected $description = 'Create a new tenant';

    public function handle(): int
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $email = $this->argument('email');

        $tenant = Tenant::create([
            'data' => [
                'name' => $name,
                'email' => $email,
                'plan' => 'basic',
            ],
        ]);

        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        $this->info("Tenant '{$name}' created successfully with domain '{$domain}'");
        $this->info("Tenant ID: {$tenant->id}");
        $this->info("Now run: php artisan tenants:migrate && php artisan tenants:seed");

        return 0;
    }
}