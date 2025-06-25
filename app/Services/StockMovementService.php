<?php

namespace App\Services;

use App\Actions\StockMovement\CreatePurchaseStockMovementsAction;
use App\Actions\StockMovement\CreateSaleStockMovementsAction;
use App\Models\Purchase;
use App\Models\Sale;
use InvalidArgumentException;

class StockMovementService
{
    public function __construct(
        protected CreatePurchaseStockMovementsAction $createPurchaseStockMovements,
        protected CreateSaleStockMovementsAction $createSaleStockMovements
    ) {}

    public function createStockMovements(Purchase|Sale $model): void
    {
        match (true) {
            $model instanceof Purchase => $this->createPurchaseStockMovements->execute($model),
            $model instanceof Sale     => $this->createSaleStockMovements->execute($model),
            default => throw new InvalidArgumentException('Unsupported model for stock movement.'),
        };
    }
}
