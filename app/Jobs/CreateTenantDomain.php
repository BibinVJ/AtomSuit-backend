<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\DomainService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTenantDomain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(DomainService $domainService): void
    {
        $domainName = $this->tenant->domain_name ?? $this->tenant->name;

        $domainService->checkDomainAvailability($domainName);

        $fullDomain = $domainService->buildFullDomain($domainName);

        $this->tenant->domains()->create(['domain' => $fullDomain]);
    }
}
