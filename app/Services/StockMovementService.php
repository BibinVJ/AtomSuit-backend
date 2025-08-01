<?php

namespace App\Services;

use App\Actions\StockMovement\CreatePurchaseStockMovementsAction;
use App\Actions\StockMovement\CreateSaleStockMovementsAction;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class StockMovementService
{
    public function __construct(
        protected CreatePurchaseStockMovementsAction $createPurchaseStockMovements,
        protected CreateSaleStockMovementsAction $createSaleStockMovements,
        protected StockMovementRepository $stockMovementRepository
    ) {}

    public function createStockMovements(Model $model): void
    {
        match (true) {
            $model instanceof Purchase => $this->createPurchaseStockMovements->execute($model),
            $model instanceof Sale => $this->createSaleStockMovements->execute($model),
            default => throw new InvalidArgumentException('Unsupported model for stock movement.'),
        };
    }

    public function reverseStockMovements(Model $model): void
    {
        foreach ($model->stockMovements as $movement) {
            $this->stockMovementRepository->create([
                ...$movement->only([
                    'item_id',
                    'batch_id',
                    'rate',
                    'standard_cost',
                    'source_type',
                    'source_id',
                ]),
                'transaction_date' => now(),
                'quantity' => -($movement->quantity), // opposie of the original movement
                'description' => "Reversal of movement ID: {$movement->id}",
                'reference' => 'VOID-'.($movement->reference ?? $model->invoice_number),
            ]);
        }
    }

    public function hasStockBeenConsumed(Purchase $purchase): bool
    {
        foreach ($purchase->items as $item) {
            $batchId = $item->batch_id;

            $consumed = StockMovement::query()
                ->where('batch_id', $batchId)
                ->where('item_id', $item->item_id)
                ->where('quantity', '<', 0)
                ->exists();

            if ($consumed) {
                return true;
            }
        }

        return false;
    }

    public function deleteStockMovements(Model $model): void
    {
        $model->stockMovements()->delete();
    }
}
