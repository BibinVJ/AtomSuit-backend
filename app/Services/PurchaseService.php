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

    /**
     * Get the next invoice number for a purchase.
     */
    public function getNextInvoiceNumber(): string
    {
        $prefix = 'P-INV';

        $lastInvoice = Purchase::whereNotNull('invoice_number')
            ->orderByDesc('id') // or created_at
            ->value('invoice_number');

        $lastNumber = 0;

        if ($lastInvoice && preg_match('/\d+$/', $lastInvoice, $matches)) {
            $lastNumber = intval($matches[0]);
        }

        $nextNumber = $lastNumber + 1;

        return "{$prefix}-".str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

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
        // check if already voided

        // Check if any stock from this purchase has been consumed
        if ($this->stockMovementService->hasStockBeenConsumed($purchase)) {
            throw new ConflictHttpException("Purchase can't be voided because stock has already been consumed.");
        }

        return $this->voidPurchase->execute($purchase);
    }
}
