<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;

class TenantSeed extends Command
{
    protected $signature = 'tenant:seed
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}
                            {--class=DatabaseSeeder : Seeder class}';

    protected $description = 'Seed tenant databases';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            $this->call('tenants:run', [
                'commandname' => 'db:seed',
                '--tenants' => [$tenantId],
                '--option' => [
                    'class='.$this->option('class'),
                ],
            ]);
        }

        return Command::SUCCESS;
    }
}
