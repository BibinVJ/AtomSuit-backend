<?php

namespace App\Services;

use App\Models\Plan;
use App\Repositories\PlanRepository;
use Exception;

class PlanService
{
    public function __construct(protected PlanRepository $planRepository) {}

    public function delete(Plan $plan)
    {
        if ($plan->subscribedTenants()->exists()) {
            throw new Exception('Plan is assigned to tenant(s) and cannot be deleted.');
        }

        $this->planRepository->delete($plan);
    }
}
