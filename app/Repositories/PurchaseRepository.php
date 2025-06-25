<?php

namespace App\Repositories;

use App\Models\Purchase;
use App\Repositories\Traits\HasCrudRepository;
use Illuminate\Database\Eloquent\Builder;

class PurchaseRepository
{
    use HasCrudRepository;

    public function __construct()
    {
        $this->model = new Purchase();
    }

    public function addItems(Purchase $purchase, array $items): void
    {
        $purchase->items()->createMany($items);
    }

}
