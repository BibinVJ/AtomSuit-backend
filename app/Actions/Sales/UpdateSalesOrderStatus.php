<?php

namespace App\Actions\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateSalesOrderStatus
{
    public function handle(SalesOrder $so, SalesOrderStatus $newStatus, ?User $updater = null): SalesOrder
    {
        if ($so->status === $newStatus) {
            return $so;
        }

        // Simple State Machine
        $allowed = match ($so->status) {
            SalesOrderStatus::CONFIRMED => [SalesOrderStatus::CANCELLED, SalesOrderStatus::DELIVERED, SalesOrderStatus::COMPLETED],
            SalesOrderStatus::PARTIALLY_DELIVERED => [SalesOrderStatus::DELIVERED, SalesOrderStatus::CANCELLED],
            SalesOrderStatus::DELIVERED => [SalesOrderStatus::COMPLETED],
            default => [],
        };

        if (! in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$so->status->value}' to '{$newStatus->value}'.",
            ]);
        }

        $so->update([
            'status' => $newStatus,
            'updated_by' => $updater?->id,
        ]);

        return $so;
    }
}
