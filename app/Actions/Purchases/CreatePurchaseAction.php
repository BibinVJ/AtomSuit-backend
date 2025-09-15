<?php

namespace App\Actions\Purchases;

use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Services\BatchService;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreatePurchaseAction
{
    public function __construct(
        protected PurchaseRepository $purchaseRepo,
        protected BatchService $batchService,
        protected StockMovementService $stockMoveService
    ) {}

    public function execute(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            /** @var Purchase $purchase */
            $purchase = $this->purchaseRepo->create($data);

            $preparedItems = collect($data['items'])->map(function ($item) {
                $batch = $this->batchService->create($item);

                return array_merge($item, ['batch_id' => $batch->id]);
            })->toArray();

            $this->purchaseRepo->addItems($purchase, $preparedItems);

            // create stock moves
            $this->stockMoveService->createStockMovements($purchase);

            return $purchase->load('items.batch', 'items.item');
        });
    }
}
