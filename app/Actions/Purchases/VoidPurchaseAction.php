<?php

namespace App\Actions\Purchases;

use App\Enums\TransactionStatus;
use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class VoidPurchaseAction
{
    public function __construct(
        protected PurchaseRepository $purchaseRepo,
        protected StockMovementService $stockMovementService,
    ) {}

    public function execute(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            if ($purchase->status === TransactionStatus::VOIDED) {
                return $purchase; // Already voided
            }

            // Create inverse stock movements
            $this->stockMovementService->reverseStockMovements($purchase);

            // Mark purchase as voided
            $this->purchaseRepo->update($purchase, ['status' => TransactionStatus::VOIDED]);

            return $purchase->fresh();
        });
    }
}
