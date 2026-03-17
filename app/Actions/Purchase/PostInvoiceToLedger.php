<?php

namespace App\Actions\Purchase;

use App\Models\PurchaseInvoice;
use App\Services\GeneralLedgerService;

class PostInvoiceToLedger
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(PurchaseInvoice $invoice): void
    {
        $totalPayable = 0;
        $glEntries = [];

        foreach ($invoice->items as $item) {
            $lineTotal = ($item->quantity * $item->unit_price) - $item->discount_amount;
            $totalPayable += $lineTotal;

            // 1. DEBIT: Inventory/Expense Account
            $debitAccount = $item->item->inventory_account_id ?? $invoice->vendor->purchase_account_id;

            if (! $debitAccount) {
                throw new \Exception("No Expense/Inventory Account found for Item {$item->item->name}");
            }

            $glEntries[] = [
                'account_id' => $debitAccount,
                'debit' => $lineTotal,
                'credit' => 0,
                'description' => $item->description ?? "Purchase of {$item->item->name}",
                'cost_center_id' => $invoice->cost_center_id, // Header Cost Center
            ];

            // 2. DEBIT: Input Tax
            if ($item->taxGroup) {
                foreach ($item->taxGroup->taxRates as $rate) {
                    $taxAmount = $lineTotal * ($rate->rate / 100);
                    $totalPayable += $taxAmount;

                    $taxAccount = $rate->purchase_account_id;
                    if (! $taxAccount) {
                        throw new \Exception("No Purchase Tax Account found for Rate {$rate->name}");
                    }

                    $glEntries[] = [
                        'account_id' => $taxAccount,
                        'debit' => $taxAmount,
                        'credit' => 0,
                        'description' => "Input Tax: {$rate->name}",
                        'cost_center_id' => $invoice->cost_center_id,
                    ];
                }
            }
        }

        // 3. CREDIT: Accounts Payable (Vendor)
        $payableAccount = $invoice->vendor->payables_account_id;
        if (! $payableAccount) {
            throw new \Exception("No Payables Account found for Vendor {$invoice->vendor->name}");
        }

        $glEntries[] = [
            'account_id' => $payableAccount,
            'debit' => 0,
            'credit' => $totalPayable,
            'description' => "Invoice #{$invoice->invoice_number}",
            'entity_type' => $invoice->vendor->getMorphClass(),
            'entity_id' => $invoice->vendor->id,
        ];

        // 4. Post Transaction
        $this->glService->postTransaction(
            $invoice,
            $invoice->posting_date,
            "Purchase Invoice #{$invoice->invoice_number}",
            $glEntries
        );
    }
}
