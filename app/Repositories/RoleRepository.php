<?php

namespace App\Repositories;

use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Role;

class RoleRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Role;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['trashed'])) {
            if ($filters['trashed'] === 'only') {
                $query->onlyTrashed();
            } elseif ($filters['trashed'] === 'with') {
                $query->withTrashed();
            }
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['exclude_roles'])) {
            $query->whereNotIn('name', $filters['exclude_roles']);
        }

        return $query;
    }
}
