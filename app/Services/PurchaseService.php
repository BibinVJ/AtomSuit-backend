<?php

namespace App\Services;

use App\Actions\CreatePurchaseAction;
use App\Models\Purchase;


class PurchaseService
{
    public function __construct(
        protected CreatePurchaseAction $createPurchase,
    ) {}


    public function create(array $data): Purchase
    {
        // additional checks if purchase can be added

        return $this->createPurchase->execute($data);
    }
}
