<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;

class TenantMigrateFresh extends Command
{
    protected $signature = 'tenant:migrate-fresh
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}';

    protected $description = 'Drop all tables and re-run all tenant migrations';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            $this->call('tenants:run', [
                'commandname' => 'migrate:fresh',
                '--tenants' => [$tenantId],
                '--option' => [
                    'path='.database_path('migrations/tenant'),
                    'realpath=true',
                ],
            ]);
        }

        return Command::SUCCESS;
    }
}
