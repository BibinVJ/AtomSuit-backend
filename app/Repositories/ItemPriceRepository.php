<?php

namespace App\Repositories;

use App\Models\ItemPrice;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class ItemPriceRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new ItemPrice;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['price_list_id'])) {
            $query->where('price_list_id', $filters['price_list_id']);
        }

        if (! empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }

        if (! empty($filters['search'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('sku', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
