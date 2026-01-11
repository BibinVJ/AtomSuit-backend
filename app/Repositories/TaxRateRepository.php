<?php

namespace App\Repositories;

use App\Models\TaxRate;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class TaxRateRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new TaxRate;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query;
    }
}
