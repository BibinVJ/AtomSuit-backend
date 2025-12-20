<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class CategoryRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Category;
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
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
