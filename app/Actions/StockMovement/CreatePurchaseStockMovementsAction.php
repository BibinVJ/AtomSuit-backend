<?php

namespace App\Actions\StockMovement;

use App\Contracts\Stock\CreateStockMovementsActionInterface;
use App\Models\Purchase;
use App\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\Model;

class CreatePurchaseStockMovementsAction implements CreateStockMovementsActionInterface
{
    public function __construct(protected StockMovementRepository $stockRepo) {}

    public function execute(Model $purchase): void
    {
        foreach ($purchase->items as $item) {
            $this->stockRepo->create([
                'item_id'         => $item->item_id,
                'batch_id'        => $item->batch_id,
                'transaction_date' => now(),
                'quantity'        => $item->quantity,
                'rate'            => $item->unit_cost,
                'standard_cost'   => $item->unit_cost,
                'source_type'     => Purchase::class,
                'source_id'       => $purchase->id,
                'description'     => 'Purchase inbound',
                'reference'       => $purchase->invoice_number,
            ]);
        }
    }
}
