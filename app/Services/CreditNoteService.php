<?php

namespace App\Services;

use App\Enums\CreditNoteStatus;
use App\Repositories\CreditNoteRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class CreditNoteService extends BaseService
{
    public function __construct(
        protected CreditNoteRepository $creditNoteRepository,
        protected GeneralLedgerService $glService,
        protected StockMovementService $stockMovementService
    ) {
        $this->repository = $creditNoteRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\CreditNote $model */
        if ($force) {
            throw new Exception('Force deleting Credit Notes is strictly prohibited. They must be voided to preserve the General Ledger and Stock Valuations.');
        }

        if ($model->status === CreditNoteStatus::VOIDED) {
            throw new Exception('Credit Note is already voided.');
        }

        // Reverse GL
        $this->glService->reverseTransaction($model, now(), "Voided Credit Note #{$model->credit_note_number}");

        // Reverse Stock (if stock was returned)
        if ($model->is_stock_returned) {
            $this->stockMovementService->reverseStockMovements($model);
        }

        // Update Status
        $model->update(['status' => CreditNoteStatus::VOIDED]);

        return true;
    }
}
