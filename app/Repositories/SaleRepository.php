<?php

namespace App\Repositories;

use App\Models\Sale;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class SaleRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Sale();
    }

    public function addItems(Sale $sale, array $items): void
    {
        $sale->items()->createMany($items);
    }

}
