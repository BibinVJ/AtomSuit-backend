<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseOrderStatus
{
    public function handle(PurchaseOrder $po, PurchaseOrderStatus $newStatus, ?User $updater = null): PurchaseOrder
    {
        // Prevent no-op
        if ($po->status === $newStatus) {
            return $po;
        }

        // Validate Transitions
        // This is a simple state machine. For complex ones, consider dedicated classes.
        $allowed = match ($po->status) {
            PurchaseOrderStatus::DRAFT => [PurchaseOrderStatus::SENT, PurchaseOrderStatus::CONFIRMED, PurchaseOrderStatus::CANCELLED],
            PurchaseOrderStatus::SENT => [PurchaseOrderStatus::CONFIRMED, PurchaseOrderStatus::CANCELLED],
            PurchaseOrderStatus::CONFIRMED => [PurchaseOrderStatus::CANCELLED], // Only if no GRN/PI linked
            default => [], // Terminating states and auto-calculated states cannot be changed manually
        };

        if (! in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$po->status->value}' to '{$newStatus->value}'.",
            ]);
        }

        if ($newStatus === PurchaseOrderStatus::CONFIRMED) {
            if ($po->items()->count() === 0) {
                throw ValidationException::withMessages(['items' => 'Cannot confirm an empty order.']);
            }
        }

        if ($newStatus === PurchaseOrderStatus::CANCELLED && $po->status === PurchaseOrderStatus::CONFIRMED) {
            // Prevent cancellation if we already received or billed against this PO
            if ($po->goodsReceivedNotes()->exists() || $po->purchaseInvoices()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Cannot cancel a confirmed order that has associated receipts or invoices. Void those documents first.',
                ]);
            }
        }

        $po->update([
            'status' => $newStatus,
            'updated_by' => $updater?->id,
        ]);

        return $po;
    }
}
