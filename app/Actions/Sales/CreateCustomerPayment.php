<?php

namespace App\Actions\Sales;

use App\Actions\GeneralLedger\PostCustomerPaymentToLedgerAction;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\User;
use App\Services\DocumentSequenceService;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateCustomerPayment
{
    public function __construct(
        protected AllocateCustomerPaymentAction $allocateAction,
        protected PostCustomerPaymentToLedgerAction $postGL,
        protected DocumentSequenceService $sequenceService
    ) {}

    public function handle(array $data, ?User $creator = null): CustomerPayment
    {
        return DB::transaction(function () use ($data, $creator) {
            $customer = Customer::findOrFail($data['customer_id']);
            $totalAmount = (float) $data['amount'];

            // 1. Validate allocations
            $allocatedTotal = 0;
            $allocations = $data['allocations'] ?? [];
            foreach ($allocations as $allocation) {
                $allocatedTotal += (float) $allocation['allocated_amount'];
            }

            if (round($allocatedTotal, 2) > round($totalAmount, 2)) {
                throw new Exception("Allocated amount ({$allocatedTotal}) cannot exceed the total payment amount ({$totalAmount}).");
            }

            // 2. Generate Payment Number if not provided
            $paymentNumber = $data['payment_number'] ?? $this->sequenceService->generateNext('customer_payment');

            // 3. Create Header
            $payment = CustomerPayment::create([
                'payment_number' => $paymentNumber,
                'customer_id' => $customer->id,
                'account_id' => $data['account_id'],
                'cost_center_id' => $data['cost_center_id'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount' => $totalAmount,
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // 4. Create Allocations
            foreach ($allocations as $allocation) {
                $payment->allocations()->create([
                    'sales_invoice_id' => $allocation['sales_invoice_id'],
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);
            }

            $this->allocateAction->applyAllocations($payment);

            // 5. Post GL
            $this->postGL->execute($payment, $totalAmount, $data['account_id']);

            return $payment;
        });
    }
}
