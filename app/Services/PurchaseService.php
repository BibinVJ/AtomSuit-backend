<?php

namespace App\Services;

use App\Actions\Purchases\CreatePurchaseAction;
use App\Actions\Purchases\UpdatePurchaseAction;
use App\Actions\Purchases\VoidPurchaseAction;
use App\Models\Purchase;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;


class PurchaseService
{
    public function __construct(
        protected CreatePurchaseAction $createPurchase,
        protected UpdatePurchaseAction $updatePurchase,
        protected VoidPurchaseAction $voidPurchase,
        protected StockMovementService $stockMovementService
    ) {}

    public function create(array $data): Purchase
    {
        // additional checks if purchase can be added

        return $this->createPurchase->execute($data);
    }

    public function update(Purchase $purchase, array $data): Purchase
    {
        // Check if any stock from this purchase has been consumed
        if ($this->stockMovementService->hasStockBeenConsumed($purchase)) {
            throw new ConflictHttpException("Purchase can't be edited because stock has already been consumed.");
        }

        return $this->updatePurchase->execute($purchase, $data);
    }

    public function void(Purchase $purchase): Purchase
    {
        // Check if any stock from this purchase has been consumed
        if ($this->stockMovementService->hasStockBeenConsumed($purchase)) {
            throw new ConflictHttpException("Purchase can't be deleted because stock has already been consumed.");
        }

        return $this->voidPurchase->execute($purchase);
    }
}
