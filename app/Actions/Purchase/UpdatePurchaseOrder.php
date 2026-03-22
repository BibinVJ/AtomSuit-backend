<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseOrder
{
    public function handle(PurchaseOrder $po, array $data, ?User $updater = null): PurchaseOrder
    {
        if ($po->status !== PurchaseOrderStatus::DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Only draft purchase orders can be updated.',
            ]);
        }

        return DB::transaction(function () use ($po, $data, $updater) {
            $po->update([
                'vendor_id' => $data['vendor_id'] ?? $po->vendor_id,
                'order_date' => $data['order_date'] ?? $po->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $po->expected_delivery_date,
                'notes' => $data['notes'] ?? $po->notes,
                'cost_center_id' => $data['cost_center_id'] ?? $po->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $po->warehouse_id,
                'reference_number' => $data['reference_number'] ?? $po->reference_number,
                'updated_by' => $updater?->id,
            ]);

            if (isset($data['items'])) {
                // Full replacement strategy for Draft orders
                $po->items()->delete();

                foreach ($data['items'] as $itemData) {
                    $po->items()->create([
                        'item_id' => $itemData['item_id'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'tax_group_id' => $itemData['tax_group_id'] ?? null,
                    ]);
                }
            }

            return $po;
        });
    }
}
