<?php

namespace App\Services;

use App\Enums\GoodsReceivedNoteStatus;
use App\Repositories\GoodsReceivedNoteRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class GoodsReceivedNoteService extends BaseService
{
    public function __construct(
        GoodsReceivedNoteRepository $repository,
        protected StockMovementService $stockMovementService
    ) {
        $this->repository = $repository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\GoodsReceivedNote $model */
        if ($force) {
            throw new Exception('Force deleting Goods Received Notes is strictly prohibited. They must be voided to preserve Stock Valuations.');
        }

        if ($model->status === GoodsReceivedNoteStatus::VOIDED) {
            throw new Exception('Goods Received Note is already voided.');
        }

        if ($model->purchaseInvoices()->exists()) {
            throw new Exception('Goods Received Note has associated Purchase Invoices and cannot be voided. Void the invoices first.');
        }

        // Reverse Stock
        $this->stockMovementService->reverseStockMovements($model);

        // Update Status
        $model->update(['status' => GoodsReceivedNoteStatus::VOIDED]);

        return true;
    }

    protected function validateForceDelete(Model $model): void
    {
        /** @var \App\Models\GoodsReceivedNote $model */
        if ($model->purchaseInvoices()->exists()) {
            throw new Exception('Goods Received Note has associated Purchase Invoices and cannot be deleted.');
        }
    }
}
