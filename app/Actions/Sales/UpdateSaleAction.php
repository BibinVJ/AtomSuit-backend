<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Repositories\SaleRepository;
use App\Services\BatchService;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class UpdateSaleAction
{
    public function __construct(
        protected SaleRepository $saleRepo,
        protected BatchService $batchService,
        protected StockMovementService $stockMovementService
    ) {}

    public function execute(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            // Delete existing stock movements
            $this->stockMovementService->deleteStockMovements($sale);

            // Update sale fields
            $this->saleRepo->update($sale, $data);

            // Update items
            $this->saleRepo->syncItems($sale, $data['items']);

            // Recreate stock movements
            $this->stockMovementService->createStockMovements($sale->refresh());

            return $sale->load('items.item');
        });
    }
}
