<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}';

    protected $description = 'Run tenant migrations (all tenants by default)';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            $this->call('tenants:run', [
                'commandname' => 'migrate',
                '--tenants'   => [$tenantId],
                '--option'    => [
                    'path=' . database_path('migrations/tenant'),
                    'realpath=true',
                ],
            ]);
        }

        return Command::SUCCESS;
    }
}
