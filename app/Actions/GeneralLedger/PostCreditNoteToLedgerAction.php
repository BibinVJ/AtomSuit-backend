<?php

namespace App\Actions\GeneralLedger;

use App\Models\CreditNote;
use App\Models\TaxGroup;
use App\Services\GeneralLedgerService;

class PostCreditNoteToLedgerAction
{
    public function __construct(
        protected GeneralLedgerService $glService
    ) {}

    public function handle(CreditNote $cn): void
    {
        $glEntries = [];

        foreach ($cn->items as $item) {
            $lineRevenue = ($item->returned_quantity * $item->unit_price) - $item->discount_amount;

            // 1. DEBIT: Sales Return Account (or Sales Revenue)
            $debitAccount = $cn->customer->sales_return_account_id ?? $cn->customer->sales_account_id;

            if (! $debitAccount) {
                throw new \Exception("No Sales Return/Revenue Account found for Customer {$cn->customer->name}");
            }

            $glEntries[] = [
                'account_id' => $debitAccount,
                'debit' => $lineRevenue,
                'credit' => 0,
                'description' => $item->description ?? "Credit Note for {$item->item->name}",
                'cost_center_id' => $cn->cost_center_id,
            ];

            // 2. DEBIT: Output Tax (Reversed)
            if ($item->tax_group_id) {
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
                            'debit' => $taxAmount,
                            'credit' => 0,
                            'description' => "Tax Credit: {$rate->name}",
                            'cost_center_id' => $cn->cost_center_id,
                        ];
                    }
                }
            }
        }

        // 3. CREDIT: Accounts Receivable (Customer)
        $receivableAccount = $cn->customer->receivables_account_id;
        if (! $receivableAccount) {
            throw new \Exception("No Receivables Account found for Customer {$cn->customer->name}");
        }

        $totalCredit = $cn->total_amount;

        $glEntries[] = [
            'account_id' => $receivableAccount,
            'debit' => 0,
            'credit' => $totalCredit,
            'description' => "Credit Note #{$cn->credit_note_number}",
            'entity_type' => $cn->customer->getMorphClass(),
            'entity_id' => $cn->customer->id,
        ];

        // 4. Post Transaction
        $this->glService->postTransaction(
            $cn,
            $cn->credit_note_date,
            "Credit Note #{$cn->credit_note_number}",
            $glEntries
        );
    }
}
