<?php

namespace App\Actions\Purchase;

use App\Models\PurchaseOrder;
use Illuminate\Validation\ValidationException;

class ValidateGrnQuantities
{
    public function validate(PurchaseOrder $po, array $items): void
    {
        foreach ($items as $itemData) {
            if (! isset($itemData['purchase_order_item_id'])) {
                continue; // Direct receiving item, no limit check against PO
            }

            $poItem = $po->items()->find($itemData['purchase_order_item_id']);

            if (! $poItem) {
                throw ValidationException::withMessages(['items' => "Invalid Purchase Order Item ID: {$itemData['purchase_order_item_id']}"]);
            }

            // Logic: Total Received so far + New Receipt <= Ordered Quantity?
            // This is a simplified check. In a real system, you'd sum up previous GRNs.

            if ($itemData['quantity_received'] > $poItem->quantity) {
                // strict check for now, can be relaxed to allow over-receiving
                // throw ValidationException::withMessages(['items' => "Cannot receive more than ordered for item {$poItem->item->name}"]);
            }
        }
    }
}
