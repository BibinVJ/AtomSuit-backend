<?php

namespace App\Services;

use App\Enums\SalesInvoiceStatus;
use App\Repositories\SalesInvoiceRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceService extends BaseService
{
    public function __construct(
        protected SalesInvoiceRepository $salesInvoiceRepository,
        protected GeneralLedgerService $glService
    ) {
        $this->repository = $salesInvoiceRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\SalesInvoice $model */
        if ($force) {
            throw new Exception('Force deleting Sales Invoices is strictly prohibited. They must be voided to preserve the General Ledger.');
        }

        if ($model->status === SalesInvoiceStatus::VOIDED) {
            throw new Exception('Sales Invoice is already voided.');
        }

        if (in_array($model->status, [SalesInvoiceStatus::PARTIALLY_PAID, SalesInvoiceStatus::PAID])) {
            throw new Exception('Sales Invoice cannot be voided because it is partially or fully paid. Void the payments first.');
        }

        if ($model->creditNotes()->where('status', '!=', 'VOIDED')->exists()) {
            throw new Exception('Sales Invoice has associated Credit Notes and cannot be voided. Void the credit notes first.');
        }

        // Reverse GL
        $this->glService->reverseTransaction($model, now(), "Voided Sales Invoice #{$model->invoice_number}");

        // Update Status
        $model->update(['status' => SalesInvoiceStatus::VOIDED]);

        return true;
    }
}
