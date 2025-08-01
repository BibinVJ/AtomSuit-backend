<?php

namespace App\Repositories;

use App\Enums\RolesEnum;
use App\Models\Unit;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class UnitRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Unit;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['is_not_admin'])) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', RolesEnum::ADMIN->value);
            });
        }

        return $query;
    }
}
