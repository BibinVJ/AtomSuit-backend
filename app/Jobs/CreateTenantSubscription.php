<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTenantSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        if (isset($this->tenant->plan_id)) {
            $subscription = Subscription::create([
                'tenant_id' => $this->tenant->id,
                'plan_id' => $this->tenant->plan_id,
                'start_date' => now(),
                'end_date' => null,
                'is_active' => true,
            ]);
        }
    }
}