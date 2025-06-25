<?php

namespace App\Services;

use App\Actions\CreateSaleAction;
use App\Models\Sale;

class SaleService
{
    public function __construct(
        protected CreateSaleAction $createSale,
    ) {}


    public function create(array $data): Sale
    {
        // additional checks if sales can be added

        return $this->createSale->execute($data);
    }
}
