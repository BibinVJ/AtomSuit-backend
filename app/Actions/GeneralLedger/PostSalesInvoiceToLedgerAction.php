<?php

namespace App\Actions\GeneralLedger;

use App\Models\SalesInvoice;
use App\Models\TaxGroup;
use App\Services\GeneralLedgerService;

class PostSalesInvoiceToLedgerAction
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(SalesInvoice $invoice): void
    {
        $totalReceivable = 0;
        $glEntries = [];

        foreach ($invoice->items as $item) {
            $lineRevenue = ($item->quantity * $item->unit_price) - $item->discount_amount;

            // 1. CREDIT: Revenue Account
            $creditAccount = $invoice->customer->sales_account_id;

            if (! $creditAccount) {
                throw new \Exception("No Sales Revenue Account found for Customer {$invoice->customer->name}");
            }

            $glEntries[] = [
                'account_id' => $creditAccount,
                'debit' => 0,
                'credit' => $lineRevenue,
                'description' => $item->description ?? "Sale of {$item->item->name}",
                'cost_center_id' => $invoice->cost_center_id,
            ];

            // 2. CREDIT: Output Tax
            if ($item->tax_group_id) {
                // Use snapshot or fresh DB? Fresh DB is safer for account IDs
                $taxGroup = TaxGroup::with('taxRates')->find($item->tax_group_id);
                if ($taxGroup) {
                    foreach ($taxGroup->taxRates as $rate) {
                        $taxAmount = $lineRevenue * ($rate->rate / 100);

                        $taxAccount = $rate->sales_account_id;
                        if (! $taxAccount) {
                            throw new \Exception("No Sales Tax Account found for Rate {$rate->name}");
                        }

                        $glEntries[] = [
                            'account_id' => $taxAccount,
                            'debit' => 0,
                            'credit' => $taxAmount,
                            'description' => "Output Tax: {$rate->name}",
                            'cost_center_id' => $invoice->cost_center_id,
                        ];
                    }
                }
            }
        }

        // 3. DEBIT: Accounts Receivable (Customer)
        $receivableAccount = $invoice->customer->receivables_account_id;
        if (! $receivableAccount) {
            throw new \Exception("No Receivables Account found for Customer {$invoice->customer->name}");
        }

        $totalReceivable = $invoice->total_amount;

        $glEntries[] = [
            'account_id' => $receivableAccount,
            'debit' => $totalReceivable,
            'credit' => 0,
            'description' => "Invoice #{$invoice->invoice_number}",
            'entity_type' => $invoice->customer->getMorphClass(),
            'entity_id' => $invoice->customer->id,
        ];

        // 4. Post Transaction
        $this->glService->postTransaction(
            $invoice,
            $invoice->invoice_date,
            "Sales Invoice #{$invoice->invoice_number}",
            $glEntries
        );
    }
}
