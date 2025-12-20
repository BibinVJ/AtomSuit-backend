<?php

namespace App\Repositories;

use App\Models\ExchangeRate;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class ExchangeRateRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new ExchangeRate;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->whereHas('baseCurrency', function ($q) use ($filters) {
                $q->where('code', 'like', '%'.$filters['search'].'%');
            })->orWhereHas('targetCurrency', function ($q) use ($filters) {
                $q->where('code', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['base_currency_id'])) {
            $query->where('base_currency_id', $filters['base_currency_id']);
        }

        if (! empty($filters['target_currency_id'])) {
            $query->where('target_currency_id', $filters['target_currency_id']);
        }

        return $query;
    }
}
