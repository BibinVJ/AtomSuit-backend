<?php

namespace App\Repositories;

use App\Models\Tenant;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class TenantRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Tenant;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['plan_id'])) {
            $query->where('plan_id', $filters['plan_id']);
        }

        return $query;
    }

    public function findByEmail(string $email): ?Tenant
    {
        return Tenant::where('email', $email)->first();
    }

    public function tenantCount(?array $statuses = null, ?array $roles = null): int
    {
        return Tenant::when($statuses, fn ($query) => $query->whereIn('status', $statuses))
            ->when($roles, fn ($query) => $query->whereHas('roles', fn ($q) => $q->whereIn('name', $roles)))
            ->count();
    }
}
