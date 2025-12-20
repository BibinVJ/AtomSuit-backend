<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class VendorRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Vendor;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {


        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query;
    }
}
