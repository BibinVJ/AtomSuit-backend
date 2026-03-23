<?php

namespace App\Actions\Purchase;

use App\Models\GoodsReceivedNoteItem;
use App\Models\PurchaseOrder;
use Illuminate\Validation\ValidationException;

class ValidateGrnQuantities
{
    public function validate(?PurchaseOrder $po, array $items): void
    {
        if (! $po) {
            // Direct GRN: no PO to validate against.
            return;
        }

        foreach ($items as $itemData) {
            if (! isset($itemData['purchase_order_item_id'])) {
                continue; // Item not linked to the PO
            }

            $poItem = $po->items()->find($itemData['purchase_order_item_id']);

            if (! $poItem) {
                throw ValidationException::withMessages(['items' => "Invalid Purchase Order Item ID: {$itemData['purchase_order_item_id']}"]);
            }

            // Calculate how much has already been received across all GRNs
            $previouslyReceived = GoodsReceivedNoteItem::where('purchase_order_item_id', $poItem->id)
                ->sum('accepted_quantity');

            $totalRequested = $previouslyReceived + $itemData['accepted_quantity'];

            if ($totalRequested > $poItem->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Cannot receive {$itemData['accepted_quantity']} for item {$poItem->item->name}. Only ".max(0, $poItem->quantity - $previouslyReceived).' remaining on order.',
                ]);
            }
        }
    }
}
