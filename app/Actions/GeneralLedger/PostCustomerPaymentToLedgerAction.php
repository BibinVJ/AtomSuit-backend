<?php

namespace App\Actions\GeneralLedger;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Services\GeneralLedgerService;
use Exception;

class PostCustomerPaymentToLedgerAction
{
    public function __construct(protected GeneralLedgerService $glService) {}

    public function execute(CustomerPayment $payment, float $totalAmount, int $accountId): void
    {
        $customer = $payment->customer;

        // Debit: Cash/Bank (Asset increases)
        // Credit: Accounts Receivable (Asset decreases)
        if (! $customer->receivables_account_id) {
            throw new Exception('Customer is missing Receivables account mapping.');
        }

        $glEntries = [
            [
                'account_id' => $accountId, // Bank / Cash asset account
                'debit' => $totalAmount,
                'credit' => 0,
                'description' => "Inbound Payment: {$payment->payment_number}",
                'entity_type' => Customer::class,
                'entity_id' => $customer->id,
                'cost_center_id' => $payment->cost_center_id,
            ],
            [
                'account_id' => $customer->receivables_account_id,
                'debit' => 0,
                'credit' => $totalAmount,
                'description' => "Payment received: {$payment->payment_number}",
                'entity_type' => Customer::class,
                'entity_id' => $customer->id,
                'cost_center_id' => $payment->cost_center_id,
            ],
        ];

        $this->glService->postTransaction(
            $payment,
            $payment->payment_date,
            "Customer Payment #{$payment->payment_number}",
            $glEntries
        );
    }
}
