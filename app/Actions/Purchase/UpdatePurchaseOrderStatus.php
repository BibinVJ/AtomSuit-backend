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
            PurchaseOrderStatus::DRAFT => [PurchaseOrderStatus::CONFIRMED, PurchaseOrderStatus::CANCELLED],
            PurchaseOrderStatus::CONFIRMED => [PurchaseOrderStatus::COMPLETED, PurchaseOrderStatus::CANCELLED],
            default => [], // Terminating states (COMPLETED, CANCELLED) cannot be changed manually usually
        };

        if (! in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$po->status->value}' to '{$newStatus->value}'.",
            ]);
        }

        // Specific Logic for certain transitions (e.g. validation before Confirm)
        if ($newStatus === PurchaseOrderStatus::CONFIRMED) {
            // Check items?
            if ($po->items()->count() === 0) {
                throw ValidationException::withMessages(['items' => 'Cannot confirm an empty order.']);
            }
        }

        $po->update([
            'status' => $newStatus,
            'updated_by' => $updater?->id,
        ]);

        return $po;
    }
}
