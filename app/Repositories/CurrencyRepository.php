<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class CurrencyRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Currency;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
