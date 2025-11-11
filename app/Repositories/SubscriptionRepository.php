<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class SubscriptionRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Subscription;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        // Filter by status
        if (isset($filters['status'])) {
            $query->where('stripe_status', $filters['status']);
        }

        // Filter by plan
        if (isset($filters['plan_id'])) {
            $query->where('plan_id', $filters['plan_id']);
        }

        // Filter by tenant (for central context)
        if (isset($filters['tenant_id'])) {
            $query->where('tenant_id', $filters['tenant_id']);
        }

        // Search by tenant name/email
        if (!empty($filters['search'])) {
            $query->whereHas('tenant', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter by active subscriptions
        if (isset($filters['is_active']) && filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)) {
            $query->whereIn('stripe_status', ['active', 'trialing']);
        }

        // Filter by canceled subscriptions
        if (isset($filters['is_canceled']) && filter_var($filters['is_canceled'], FILTER_VALIDATE_BOOLEAN)) {
            $query->where('stripe_status', 'canceled');
        }

        return $query;
    }
}
