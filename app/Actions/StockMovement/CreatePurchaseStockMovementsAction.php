<?php

namespace App\Actions\StockMovement;

use App\Models\PurchaseOrder;
use App\Repositories\StockMovementRepository;

class CreatePurchaseStockMovementsAction
{
    public function __construct(protected StockMovementRepository $stockRepo) {}

    public function execute(PurchaseOrder $purchase): void
    {
        /** @var \App\Models\PurchaseOrderItem $item */
        foreach ($purchase->items as $item) {
            $this->stockRepo->create([
                'item_id' => $item->item_id,
                'batch_id' => $item->batch_id,
                'transaction_date' => now(),
                'quantity' => $item->quantity,
                'rate' => $item->unit_cost,
                'standard_cost' => $item->unit_cost,
                'source_type' => PurchaseOrder::class,
                'source_id' => $purchase->id,
                'description' => 'Purchase inbound',
                'reference' => $purchase->invoice_number,
            ]);
        }
    }
}
