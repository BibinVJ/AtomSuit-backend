<?php

namespace App\Repositories;

use App\Models\AccountGroup;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class AccountGroupRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new AccountGroup;
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

        if (! empty($filters['account_type_id'])) {
            $query->where('account_type_id', $filters['account_type_id']);
        }

        return $query;
    }
}
