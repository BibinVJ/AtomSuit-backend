<?php

namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class WarehouseRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Warehouse;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%')
                    ->orWhere('city', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
