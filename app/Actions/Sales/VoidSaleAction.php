<?php

namespace App\Actions\Sales;

use App\Enums\TransactionStatus;
use App\Models\Sale;
use App\Repositories\SaleRepository;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class VoidSaleAction
{
    public function __construct(
        protected SaleRepository $saleRepo,
        protected StockMovementService $stockMovementService,
    ) {}

    public function execute(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            if ($sale->status === TransactionStatus::VOIDED) {
                return $sale; // Already voided
            }

            // Create inverse stock movements
            $this->stockMovementService->reverseStockMovements($sale);

            // Mark sale as voided
            $this->saleRepo->update($sale, ['status' => TransactionStatus::VOIDED]);
        });
    }
}
