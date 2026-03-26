<?php

namespace App\Actions\StockMovement;

use App\Models\DebitNote;
use App\Repositories\StockMovementRepository;

class CreateDebitNoteStockMovementsAction
{
    public function __construct(
        protected StockMovementRepository $stockRepo
    ) {}

    public function execute(DebitNote $debitNote): void
    {
        /** @var \App\Models\DebitNoteItem $item */
        foreach ($debitNote->items as $item) {
            if (! $item->is_stock_returned) {
                continue;
            }

            $this->stockRepo->create([
                'item_id' => $item->item_id,
                'batch_id' => $item->batch_id,
                'transaction_date' => $debitNote->date ?? now(),
                'quantity' => -($item->quantity), // We are pulling stock out
                'rate' => $item->unit_price,
                'standard_cost' => $item->unit_price,
                'source_type' => DebitNote::class,
                'source_id' => $debitNote->id,
                'description' => 'Goods returned / debited to vendor',
                'reference' => $debitNote->debit_note_number,
                'warehouse_id' => $debitNote->warehouse_id,
                'cost_center_id' => $debitNote->cost_center_id,
            ]);
        }
    }
}
