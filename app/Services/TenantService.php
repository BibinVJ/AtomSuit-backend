<?php

namespace App\Services;

use App\Enums\TenantStatusEnum;
use App\Models\Plan;
use App\Models\Tenant;
use App\Repositories\TenantRepository;
use Illuminate\Support\Facades\Hash;

class TenantService
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected DomainService $domainService
    ) {}

    public function getStats(): array
    {
        $stats = [
            'total' => $this->tenantRepository->count(),
            'active' => $this->tenantRepository->countActive(),
            'inactive' => $this->tenantRepository->countInactive(),
            'trial' => $this->tenantRepository->countTrial(),
            'grace_period' => $this->tenantRepository->countGracePeriod(),
            'expired' => $this->tenantRepository->countExpired(),
        ];

        return $stats;
    }

    public function create(array $data): Tenant
    {
        $plan = Plan::findOrFail($data['plan_id']);

        //check domain availability
        $this->domainService->checkDomainAvailability($data['domain_name']);

        $tenant = Tenant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => TenantStatusEnum::ACTIVE->value,
            'trial_ends_at' => $plan->is_trial_plan ? now()->addDays($plan->trial_duration_in_days) : null,
            'grace_period_ends_at' => null,
            'plan_id' => $plan->id,
            'domain_name' => $data['domain_name'],
            'load_sample_data' => $data['load_sample_data'],
        ]);

        return $tenant;
    }

    public function delete(Tenant $tenant): void
    {
        // add check for if tenant is deletable

        $this->tenantRepository->delete($tenant);
    }
}
