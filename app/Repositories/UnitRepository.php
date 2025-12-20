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


        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
