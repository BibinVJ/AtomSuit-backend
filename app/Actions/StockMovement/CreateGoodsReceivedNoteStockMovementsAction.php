<?php

namespace App\Actions\StockMovement;

use App\Models\GoodsReceivedNote;
use App\Repositories\StockMovementRepository;

class CreateGoodsReceivedNoteStockMovementsAction
{
    public function __construct(protected StockMovementRepository $stockRepo) {}

    public function execute(GoodsReceivedNote $grn): void
    {
        /** @var \App\Models\GoodsReceivedNoteItem $item */
        foreach ($grn->items as $item) {
            $this->stockRepo->create([
                'item_id' => $item->item_id,
                'batch_id' => null, // Placeholder for future batch tracking support on GRNs
                'transaction_date' => $grn->received_date ?? now(),
                'quantity' => $item->accepted_quantity,
                'rate' => $item->unit_price,
                'standard_cost' => $item->unit_price,
                'source_type' => GoodsReceivedNote::class,
                'source_id' => $grn->id,
                'description' => 'Goods received from vendor '.($grn->vendor->name ?? ''),
                'reference' => $grn->grn_number,
            ]);
        }
    }
}
