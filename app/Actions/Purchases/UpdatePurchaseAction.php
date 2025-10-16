<?php

namespace App\Actions\Purchases;

use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Services\BatchService;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class UpdatePurchaseAction
{
    public function __construct(
        protected PurchaseRepository $purchaseRepo,
        protected BatchService $batchService,
        protected StockMovementService $stockMovementService
    ) {}

    public function execute(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            // Delete existing stock movements
            $this->stockMovementService->deleteStockMovements($purchase);

            // Update purchase fields
            $this->purchaseRepo->update($purchase, $data);

            // add batch
            $preparedItems = collect($data['items'])->map(function ($item) {
                $batch = $this->batchService->create($item);

                return array_merge($item, ['batch_id' => $batch->id]);
            })->toArray();

            $this->purchaseRepo->syncItems($purchase, $preparedItems);

            // Recreate stock movements
            $this->stockMovementService->createStockMovements($purchase);

            return $purchase->load('items.item', 'items.batch');
        });
    }
}
