<?php

namespace App\Services;

use App\Actions\Sales\AllocateCustomerPaymentAction;
use App\Enums\CustomerPaymentStatus;
use App\Models\SalesInvoice;
use App\Repositories\CustomerPaymentRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;

class CustomerPaymentService extends BaseService
{
    public function __construct(
        protected CustomerPaymentRepository $customerPaymentRepository,
        protected GeneralLedgerService $glService,
        protected AllocateCustomerPaymentAction $allocator
    ) {
        $this->repository = $customerPaymentRepository;
    }

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\CustomerPayment $model */
        if ($force) {
            throw new Exception('Force deleting Customer Payments is strictly prohibited. They must be voided to preserve the General Ledger.');
        }

        if ($model->status === CustomerPaymentStatus::VOIDED) {
            throw new Exception('Customer Payment is already voided.');
        }

        // 1. Extract invoice IDs before voiding so we know which ones to recalculate
        $invoiceIds = $model->allocations->pluck('sales_invoice_id')->unique()->filter();

        // 2. Reverse GL
        $this->glService->reverseTransaction($model, now(), "Voided Customer Payment #{$model->payment_number}");

        // 3. Update Status
        $model->update(['status' => CustomerPaymentStatus::VOIDED]);

        // 4. Force linked invoices to recalculate their status and balances
        foreach ($invoiceIds as $invId) {
            $invoice = SalesInvoice::find($invId);
            if ($invoice) {
                $this->allocator->recalculateInvoiceStatus($invoice);
            }
        }

        return true;
    }
}
