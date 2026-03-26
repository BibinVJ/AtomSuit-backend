<?php

namespace App\Services;

use App\Actions\StockMovement\CreateCreditNoteStockMovementsAction;
use App\Actions\StockMovement\CreateDebitNoteStockMovementsAction;
use App\Actions\StockMovement\CreateDeliveryNoteStockMovementsAction;
use App\Actions\StockMovement\CreateGoodsReceivedNoteStockMovementsAction;
use App\Models\CreditNote;
use App\Models\DebitNote;
use App\Models\DeliveryNote;
use App\Models\GoodsReceivedNote;
use App\Models\StockMovement;
use App\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class StockMovementService
{
    public function __construct(
        protected CreateGoodsReceivedNoteStockMovementsAction $createGrnStockMovements,
        protected CreateDeliveryNoteStockMovementsAction $createDnStockMovements,
        protected CreateDebitNoteStockMovementsAction $createDebitNoteStockMovements,
        protected CreateCreditNoteStockMovementsAction $createCreditNoteStockMovements,
        protected StockMovementRepository $stockMovementRepository
    ) {}

    public function createStockMovements(Model $model): void
    {
        match (true) {
            $model instanceof GoodsReceivedNote => $this->createGrnStockMovements->execute($model),
            $model instanceof DeliveryNote => $this->createDnStockMovements->execute($model),
            $model instanceof DebitNote => $this->createDebitNoteStockMovements->execute($model),
            $model instanceof CreditNote => $this->createCreditNoteStockMovements->execute($model),
            default => throw new InvalidArgumentException('Unsupported model for stock movement.'),
        };
    }

    public function reverseStockMovements(Model $model): void
    {
        /** @var \App\Models\GoodsReceivedNote $model */
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
                'reference' => 'VOID-'.($movement->reference ?? ($model->grn_number ?? $model->invoice_number ?? 'UNKNOWN')),
            ]);
        }
    }

    public function hasStockBeenConsumed(GoodsReceivedNote $grn): bool
    {
        /** @var \App\Models\GoodsReceivedNoteItem $item */
        foreach ($grn->items as $item) {
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
}
