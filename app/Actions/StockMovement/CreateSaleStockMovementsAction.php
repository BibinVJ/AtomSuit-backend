<?php

namespace App\Actions\StockMovement;

use App\Contracts\Stock\CreateStockMovementsActionInterface;
use App\Models\Sale;
use App\Repositories\StockMovementRepository;
use Illuminate\Database\Eloquent\Model;

class CreateSaleStockMovementsAction implements CreateStockMovementsActionInterface
{
    public function __construct(protected StockMovementRepository $stockRepo) {}

    public function execute(Model $sale): void
    {
        foreach ($sale->items as $item) {
            $this->stockRepo->create([
                'item_id'         => $item->item_id,
                'batch_id'        => $item->batch_id,
                'transaction_date' => now(),
                'quantity'        => -($item->quantity), // -ve for sale
                'rate'            => $item->unit_price,
                'standard_cost'   => null,
                'source_type'     => Sale::class,
                'source_id'       => $sale->id,
                'description'     => 'Sale outbound',
                'reference'       => $sale->invoice_number ?? null,
            ]);
        }
    }
}
