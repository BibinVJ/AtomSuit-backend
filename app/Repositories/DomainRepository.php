<?php

namespace App\Repositories;

use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;
use Stancl\Tenancy\Database\Models\Domain;

class DomainRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Domain;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('domain', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
