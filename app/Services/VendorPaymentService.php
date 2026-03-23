<?php

namespace App\Services;

use App\Actions\Purchase\AllocateVendorPaymentAction;
use App\Enums\VendorPaymentStatus;
use Exception;
use Illuminate\Database\Eloquent\Model;

class VendorPaymentService extends BaseService
{
    public function __construct(
        protected GeneralLedgerService $glService,
        protected AllocateVendorPaymentAction $allocator
    ) {}

    public function delete(Model $model, bool $force = false)
    {
        /** @var \App\Models\VendorPayment $model */
        if ($force) {
            throw new Exception('Force deleting Vendor Payments is strictly prohibited. They must be voided to accurately reverse General Ledger bank allocations.');
        }

        if ($model->status === VendorPaymentStatus::VOIDED) {
            throw new Exception('Vendor Payment is already voided.');
        }

        // 1. Extract invoice IDs before voiding so we know which ones to recalculate
        $invoiceIds = $model->allocations->pluck('purchase_invoice_id')->unique()->filter();

        // 2. Reverse DB General Ledger
        $this->glService->reverseTransaction($model, now(), "Voided Vendor Payment #{$model->payment_number}");

        // 3. Drop status to Voided
        $model->update(['status' => VendorPaymentStatus::VOIDED]);

        // 4. Force linked invoices to recalculate their status (Now that this payment is VOIDED, it won't count)
        foreach ($invoiceIds as $invId) {
            $invoice = \App\Models\PurchaseInvoice::find($invId);
            if ($invoice) {
                $this->allocator->recalculateInvoiceStatus($invoice);
            }
        }

        return true;
    }

    protected function validateForceDelete(Model $model): void
    {
        throw new Exception('Force deleting Vendor Payments is strictly prohibited.');
    }
}
