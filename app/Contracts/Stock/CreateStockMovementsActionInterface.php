<?php

namespace App\Contracts\Stock;

use Illuminate\Database\Eloquent\Model;

interface CreateStockMovementsActionInterface
{
    public function execute(Model $model): void;
}
