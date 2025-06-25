<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Jobs\CreateStockMovementsJob;
use App\Models\Sale;
use App\Repositories\SaleRepository;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreateSaleAction
{
    public function __construct(
        protected SaleRepository $saleRepo,
        protected StockMovementService $stockMoveService
    ) {}

    public function execute(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = $this->saleRepo->create([
                'customer_id'    => $data['customer_id'],
                'invoice_number' => $data['invoice_number'],
                'sale_date'  => $data['sale_date'],
                'payment_status' => $data['payment_status'] ?? PaymentStatus::PENDING,
            ]);

            $this->saleRepo->addItems($sale, $data['items']);

            // add stock movements for the sale
            // dispatch(new CreateStockMovementsJob($sale));
            $this->stockMoveService->createStockMovements($sale);

            return $sale->load('items.batch', 'items.item');
        });
    }
}
