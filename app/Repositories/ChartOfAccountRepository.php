<?php

namespace App\Repositories;

use App\Models\ChartOfAccount;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class ChartOfAccountRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new ChartOfAccount;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['account_group_id'])) {
            $query->where('account_group_id', $filters['account_group_id']);
        }

        return $query;
    }
}
