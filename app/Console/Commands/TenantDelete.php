<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantSelector;
use Illuminate\Console\Command;

class TenantDelete extends Command
{
    protected $signature = 'tenant:delete
                            {--ids= : Comma-separated tenant IDs}
                            {--emails= : Comma-separated tenant emails}';

    protected $description = 'Delete tenant(s), including database and storage';

    public function handle(): int
    {
        $tenantIds = TenantSelector::resolve($this->option('ids'), $this->option('emails'));

        foreach ($tenantIds as $tenantId) {
            $tenant = Tenant::find($tenantId);
            if (! $tenant) {
                $this->warn("Tenant with ID {$tenantId} not found, skipping.");

                continue;
            }

            $tenantName = $tenant->name;
            $tenant->delete(); // Trigger TenantDeleted event, which runs the pipeline

            $this->info("Tenant '{$tenantName}' (ID: {$tenantId}) deleted successfully.");
        }

        return self::SUCCESS;
    }
}
