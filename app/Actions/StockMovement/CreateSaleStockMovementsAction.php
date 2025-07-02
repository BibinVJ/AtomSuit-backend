<?php

namespace App\Actions\StockMovement;

use App\Contracts\Stock\CreateStockMovementsActionInterface;
use App\Models\Sale;
use App\Repositories\StockMovementRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class CreateSaleStockMovementsAction implements CreateStockMovementsActionInterface
{
    public function __construct(protected StockMovementRepository $stockMovementRepo) {}

    public function execute(Model $sale): void
    {
        foreach ($sale->items as $saleItem) {
            $remainingQty = $saleItem->quantity;

            // Fetch stock movements (FIFO) from earliest unsold purchases
            $batches = $this->stockMovementRepo->getFifoAvailableStock(['item_id' => $saleItem->item_id]);

            foreach ($batches as $batch) {
                if ($remainingQty <= 0) break;

                $available = $batch->available_qty;
                $toDeduct = min($available, $remainingQty);

                $this->stockMovementRepo->create([
                    'item_id'         => $saleItem->item_id,
                    'batch_id'        => $batch->batch_id,
                    'transaction_date' => $sale->sale_date,
                    'quantity'        => -($toDeduct),
                    'rate'            => $saleItem->unit_price,
                    'standard_cost'   => $saleItem->unit_price, // Or calculate avg cost
                    'source_type'     => Sale::class,
                    'source_id'       => $sale->id,
                    'description'     => 'Sale of item',
                    'reference'       => $sale->invoice_number,
                ]);

                $remainingQty -= $toDeduct;
            }

            if ($remainingQty > 0) {
                throw new Exception("Not enough stock for item {$saleItem->item->name}", Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }
}
