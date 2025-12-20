<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;

class TenantRollback extends Command
{
    protected $signature = 'tenant:rollback
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}
                            {--step=1 : Number of steps to rollback}';

    protected $description = 'Rollback tenant migrations';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            $this->call('tenants:run', [
                'commandname' => 'migrate:rollback',
                '--tenants' => [$tenantId],
                '--option' => [
                    'step='.$this->option('step'),
                    'path='.database_path('migrations/tenant'),
                    'realpath=true',
                ],
            ]);
        }

        return Command::SUCCESS;
    }
}
