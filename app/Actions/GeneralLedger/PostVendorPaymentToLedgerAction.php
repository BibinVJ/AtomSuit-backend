<?php

namespace App\Actions\GeneralLedger;

use App\Models\Vendor;
use App\Models\VendorPayment;
use App\Services\GeneralLedgerService;
use Exception;

class PostVendorPaymentToLedgerAction
{
    public function __construct(protected GeneralLedgerService $glService) {}

    public function execute(VendorPayment $vendorPayment, float $totalAmount, int $accountId): void
    {
        $vendor = $vendorPayment->vendor;
        if (! $vendor) {
            $vendor = Vendor::findOrFail($vendorPayment->vendor_id);
        }

        // Debit: AP (we owe less)
        // Credit: Cash/Bank (we have less cash)
        if (! $vendor->payables_account_id) {
            throw new Exception('Vendor is missing Payables account mapping.');
        }

        $glEntries = [
            [
                'account_id' => $vendor->payables_account_id,
                'debit' => $totalAmount,
                'credit' => 0,
                'description' => "Payment applied: {$vendorPayment->payment_number}",
                'entity_type' => Vendor::class,
                'entity_id' => $vendor->id,
                'cost_center_id' => null,
            ],
            [
                'account_id' => $accountId, // Bank / Cash asset account
                'debit' => 0,
                'credit' => $totalAmount,
                'description' => "Outbound Payment: {$vendorPayment->payment_number}",
                'entity_type' => Vendor::class,
                'entity_id' => $vendor->id,
                'cost_center_id' => null,
            ],
        ];

        $this->glService->postTransaction(
            $vendorPayment,
            $vendorPayment->payment_date,
            "Vendor Payment #{$vendorPayment->payment_number}",
            $glEntries
        );
    }
}
