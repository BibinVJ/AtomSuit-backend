<?php

namespace App\Services;

use App\Enums\DeliveryNoteStatus;
use App\Repositories\DeliveryNoteRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class DeliveryNoteService extends BaseService
{
    public function __construct(
        protected DeliveryNoteRepository $deliveryNoteRepository,
        protected StockMovementService $stockMovementService
    ) {
        $this->repository = $deliveryNoteRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\DeliveryNote $model */
        if ($force) {
            throw new Exception('Force deleting Delivery Notes is strictly prohibited. They must be voided to preserve Stock Valuations.');
        }

        if ($model->status === DeliveryNoteStatus::VOIDED) {
            throw new Exception('Delivery Note is already voided.');
        }

        if ($model->salesInvoices()->exists()) {
            throw new Exception('Delivery Note has associated Invoices and cannot be voided. Void the invoices first.');
        }

        // Reverse Stock
        $this->stockMovementService->reverseStockMovements($model);

        // Update Status
        $model->update(['status' => DeliveryNoteStatus::VOIDED]);

        return true;
    }
}
