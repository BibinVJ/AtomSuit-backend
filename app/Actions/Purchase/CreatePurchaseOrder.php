<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
    public function handle(array $data, ?User $creator = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $creator) {

            // Optional: Add specific business validation here
            // e.g. Check if cost center is valid for this user

            $po = PurchaseOrder::create([
                'vendor_id' => $data['vendor_id'],
                'order_number' => $data['order_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => PurchaseOrderStatus::DRAFT,
                'notes' => $data['notes'] ?? null,
                'cost_center_id' => $data['cost_center_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

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

            return $po;
        });
    }
}
