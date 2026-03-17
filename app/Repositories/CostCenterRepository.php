<?php

namespace App\Repositories;

use App\Models\CostCenter;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class CostCenterRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new CostCenter;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query;
    }
}
