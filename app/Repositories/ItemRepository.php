<?php

namespace App\Repositories;

use App\Models\Item;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class ItemRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Item;
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


        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('sku', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('category', function ($catQ) use ($filters) {
                        $catQ->where('name', 'like', '%'.$filters['search'].'%');
                    })
                    ->orWhereHas('unit', function ($unitQ) use ($filters) {
                        $unitQ->where('name', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        return $query;
    }
}
