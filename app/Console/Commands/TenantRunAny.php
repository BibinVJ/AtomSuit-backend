<?php

namespace App\Console\Commands;

use App\Services\TenantSelector;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TenantRunAny extends Command
{
    protected $signature = 'tenant:run-any {artisanCommand* : Command to run (with args/options)}
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}';

    protected $description = 'Run any artisan command for tenant(s)';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));
        $artisanCommand = $this->argument('artisanCommand');

        foreach ($tenantIds as $tenantId) {
            $this->info("Running on tenant {$tenantId}");

            tenancy()->initialize($tenantId);
            Artisan::call(implode(' ', $artisanCommand));
            $this->line(Artisan::output());
            tenancy()->end();
        }

        return Command::SUCCESS;
    }
}
