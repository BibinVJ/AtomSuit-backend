<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\ModuleManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTenantModules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(ModuleManager $moduleManager): void
    {
        $plan = $this->tenant->plan;

        if (! $plan) {
            Log::warning('SyncTenantModules: No plan found for tenant', [
                'tenant_id' => $this->tenant->getTenantKey(),
            ]);

            return;
        }

        $moduleManager->syncFromPlan($this->tenant, $plan);

        Log::info('SyncTenantModules: Modules synced for tenant', [
            'tenant_id' => $this->tenant->getTenantKey(),
            'plan_id' => $plan->id,
            'modules' => $this->tenant->modules()->pluck('slug')->toArray(),
        ]);
    }
}
