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
        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('sku', 'like', '%'.$filters['search'].'%');
                // ->orWhereHas('category', fn($catQ) =>
                //     $catQ->where('name', 'like', '%' . $filters['search'] . '%'))
                // ->orWhereHas('unit', fn($unitQ) =>
                //     $unitQ->where('name', 'like', '%' . $filters['search'] . '%'));
            });
        }

        return $query;
    }
}
