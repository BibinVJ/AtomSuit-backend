<?php

namespace App\Actions;

use App\Enums\PaymentStatus;
use App\Jobs\CreateStockMovementsJob;
use App\Models\Purchase;
use App\Repositories\PurchaseRepository;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreatePurchaseAction
{
    public function __construct(
        protected PurchaseRepository $purchaseRepo,
        protected StockMovementService $stockMoveService
    ) {}

    public function execute(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            $purchase = $this->purchaseRepo->create([
                'vendor_id'      => $data['vendor_id'],
                'invoice_number' => $data['invoice_number'],
                'purchase_date'  => $data['purchase_date'],
                'payment_status' => $data['payment_status'] ?? PaymentStatus::PENDING,
            ]);

            $this->purchaseRepo->addItems($purchase, $data['items']);

            // add stock movements for the purchase
            // dispatch(new CreateStockMovementsJob($purchase));
            $this->stockMoveService->createStockMovements($purchase);

            return $purchase->load('items.batch', 'items.item');
        });
    }
}
