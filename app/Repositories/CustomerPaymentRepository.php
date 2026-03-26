<?php

namespace App\Repositories;

use App\Models\CustomerPayment;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class CustomerPaymentRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new CustomerPayment;
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }
}
