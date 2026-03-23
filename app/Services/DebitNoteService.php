<?php

namespace App\Services;

use App\Enums\DebitNoteStatus;
use App\Repositories\DebitNoteRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class DebitNoteService extends BaseService
{
    public function __construct(
        DebitNoteRepository $repository,
        protected GeneralLedgerService $glService,
        protected StockMovementService $stockMovementService
    ) {
        $this->repository = $repository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\DebitNote $model */
        if ($force) {
            throw new Exception('Force deleting Debit Notes is strictly prohibited. They must be voided to preserve the General Ledger and Stock Validations.');
        }

        if ($model->status === DebitNoteStatus::VOIDED) {
            throw new Exception('Debit Note is already voided.');
        }

        // Reverse GL
        $this->glService->reverseTransaction($model, now(), "Voided Debit Note #{$model->debit_note_number}");

        // Reverse Stock if it was deducted
        if ($model->is_stock_returned) {
            $this->stockMovementService->reverseStockMovements($model);
        }

        // Update Status
        $model->update(['status' => DebitNoteStatus::VOIDED]);

        return true;
    }

    protected function validateForceDelete(Model $model): void
    {
        // Force delete disabled
    }
}
