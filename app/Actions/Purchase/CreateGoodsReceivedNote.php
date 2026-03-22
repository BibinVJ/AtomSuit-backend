<?php

namespace App\Actions\Purchase;

use App\Enums\GoodsReceivedNoteStatus;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreateGoodsReceivedNote
{
    public function __construct(
        protected ValidateGrnQuantities $validator,
        protected StockMovementService $stockService // We can refactor this to an Action later too
    ) {}

    public function handle(?PurchaseOrder $po, array $data, ?User $creator = null): GoodsReceivedNote
    {
        // 1. Validate Quantities (handles null PO safely)
        ($this->validator)->validate($po, $data['items']);

        return DB::transaction(function () use ($po, $data, $creator) {

            // 2. Create Header
            $grn = GoodsReceivedNote::create([
                'purchase_order_id' => $po?->id,
                'vendor_id' => $data['vendor_id'] ?? $po?->vendor_id,
                'grn_number' => $data['grn_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'received_date' => $data['received_date'],
                'status' => GoodsReceivedNoteStatus::RECEIVED,
                'notes' => $data['notes'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? $po?->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $po?->warehouse_id,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // 3. Create Items
            foreach ($data['items'] as $itemData) {
                $poItem = null;
                if ($po && isset($itemData['purchase_order_item_id'])) {
                    $poItem = $po->items()->find($itemData['purchase_order_item_id']);
                }

                $unitPrice = $poItem ? $poItem->unit_price : ($itemData['unit_price'] ?? 0);
                $discountAmount = $poItem ? $poItem->discount_amount : ($itemData['discount_amount'] ?? 0);
                $taxGroupId = $poItem ? $poItem->tax_group_id : ($itemData['tax_group_id'] ?? null);

                $grn->items()->create([
                    'item_id' => $itemData['item_id'],
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'description' => $itemData['description'] ?? $poItem?->description,
                    'quantity_received' => $itemData['quantity_received'],
                    'accepted_quantity' => $itemData['accepted_quantity'],
                    'rejected_quantity' => $itemData['rejected_quantity'] ?? 0,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // 4. Update Stock
            $this->stockService->createStockMovements($grn);

            // 5. Update PO Status
            if ($po) {
                $this->updatePurchaseOrderStatus($po);
            }

            return $grn;
        });
    }

    protected function updatePurchaseOrderStatus(PurchaseOrder $po): void
    {
        $allItemsReceived = true;
        $anyItemsReceived = false;

        foreach ($po->items as $poItem) {
            $receivedQty = \App\Models\GoodsReceivedNoteItem::where('purchase_order_item_id', $poItem->id)
                ->sum('accepted_quantity');

            if ($receivedQty > 0) {
                $anyItemsReceived = true;
            }

            if ($receivedQty < $poItem->quantity) {
                $allItemsReceived = false;
            }
        }

        if ($allItemsReceived) {
            $po->update(['status' => \App\Enums\PurchaseOrderStatus::RECEIVED]);
        } elseif ($anyItemsReceived) {
            $po->update(['status' => \App\Enums\PurchaseOrderStatus::PARTIALLY_RECEIVED]);
        }
    }
}
