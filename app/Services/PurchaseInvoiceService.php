<?php

namespace App\Services;

use App\Enums\PurchaseInvoiceStatus;
use App\Repositories\PurchaseInvoiceRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceService extends BaseService
{
    public function __construct(
        PurchaseInvoiceRepository $repository,
        protected GeneralLedgerService $glService
    ) {
        $this->repository = $repository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\PurchaseInvoice $model */
        if ($force) {
            throw new Exception('Force deleting Purchase Invoices is strictly prohibited. They must be voided to preserve the General Ledger.');
        }

        if ($model->status === PurchaseInvoiceStatus::VOIDED) {
            throw new Exception('Purchase Invoice is already voided.');
        }

        if (in_array($model->status, [PurchaseInvoiceStatus::PARTIALLY_PAID, PurchaseInvoiceStatus::PAID])) {
            throw new Exception('Purchase Invoice cannot be voided because it is partially or fully paid. Void the payments first.');
        }

        // Reverse GL
        $this->glService->reverseTransaction($model, now(), "Voided Invoice #{$model->invoice_number}");

        // Update Status
        $model->update(['status' => PurchaseInvoiceStatus::VOIDED]);

        return true;
    }

    protected function validateForceDelete(Model $model): void
    {
        // No dependent records currently block PI deletion (payments would).
    }
}
