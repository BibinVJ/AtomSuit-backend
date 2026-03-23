<?php

namespace App\Actions\Purchase;

use App\Actions\GeneralLedger\PostVendorPaymentToLedgerAction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateVendorPayment
{
    public function __construct(
        protected AllocateVendorPaymentAction $allocateAction,
        protected PostVendorPaymentToLedgerAction $postVendorPaymentGL
    ) {}

    public function handle(array $data, ?User $creator = null): VendorPayment
    {
        return DB::transaction(function () use ($data, $creator) {
            $vendor = Vendor::findOrFail($data['vendor_id']);
            $totalAmount = (float) $data['amount'];

            // 1. Validate allocations total does not exceed the payment amount
            $allocatedTotal = 0;
            $allocations = $data['allocations'] ?? [];
            foreach ($allocations as $allocation) {
                $allocatedTotal += (float) $allocation['allocated_amount'];
            }

            if (round($allocatedTotal, 2) > round($totalAmount, 2)) {
                throw new Exception("Allocated amount ({$allocatedTotal}) cannot exceed the total payment amount ({$totalAmount}).");
            }

            // 2. Generate Payment Number if not provided
            $paymentNumber = $data['payment_number'] ?? $this->generatePaymentNumber();

            // 3. Create the Vendor Payment Header
            $vendorPayment = VendorPayment::create([
                'payment_number' => $paymentNumber,
                'vendor_id' => $vendor->id,
                'account_id' => $data['account_id'],
                'payment_method' => $data['payment_method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'amount' => $totalAmount,
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // 4. Create Allocations & Update Invoices
            foreach ($allocations as $allocation) {
                $vendorPayment->allocations()->create([
                    'purchase_invoice_id' => $allocation['purchase_invoice_id'],
                    'allocated_amount' => $allocation['allocated_amount'],
                ]);
            }

            $this->allocateAction->applyAllocations($vendorPayment);

            // 5. Post to General Ledger
            $this->postVendorPaymentGL->execute($vendorPayment, $totalAmount, $data['account_id']);

            return $vendorPayment;
        });
    }

    private function generatePaymentNumber(): string
    {
        $nextId = VendorPayment::max('id') + 1;
        $prefix = 'VPAY-'.now()->format('Ym').'-';

        return $prefix.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);
    }
}
