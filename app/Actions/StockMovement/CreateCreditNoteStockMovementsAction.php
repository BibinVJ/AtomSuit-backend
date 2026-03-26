<?php

namespace App\Actions\StockMovement;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Repositories\StockMovementRepository;

class CreateCreditNoteStockMovementsAction
{
    public function __construct(
        protected StockMovementRepository $stockRepo
    ) {}

    public function execute(CreditNote $creditNote): void
    {
        // For Sales Returns (Credit Note), stock comes BACK in (Positive quantity)
        /** @var CreditNoteItem $item */
        foreach ($creditNote->items as $item) {
            // Check per-item return flag (matching Debit Note pattern)
            if (! $item->is_stock_returned) {
                continue;
            }

            $this->stockRepo->create([
                'item_id' => $item->item_id,
                'batch_id' => $item->batch_id,
                'transaction_date' => $creditNote->credit_note_date ?? now(),
                'quantity' => $item->returned_quantity, // Recovery of stock (positive)
                'rate' => $item->unit_price,
                'standard_cost' => $item->unit_price,
                'source_type' => CreditNote::class,
                'source_id' => $creditNote->id,
                'description' => 'Goods returned from customer / Credit Note issued',
                'reference' => $creditNote->credit_note_number,
                'warehouse_id' => $creditNote->warehouse_id,
                'cost_center_id' => $creditNote->cost_center_id,
            ]);
        }
    }
}
