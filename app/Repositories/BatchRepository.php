<?php

namespace App\Repositories;

use App\Models\Batch;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class BatchRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Batch();
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('batch_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query;
    }
}
