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

            $vendorId = $data['vendor_id'] ?? $po?->vendor_id;
            $vendor = \App\Models\Vendor::find($vendorId);
            $vendorMeta = $vendor ? [
                'name' => $vendor->name,
                'email' => $vendor->email,
                'phone' => $vendor->phone,
                'billing_address_line_1' => $vendor->billing_address_line_1,
                'billing_address_line_2' => $vendor->billing_address_line_2,
                'billing_city' => $vendor->billing_city,
                'billing_state' => $vendor->billing_state,
                'billing_country' => $vendor->billing_country,
                'billing_zip_code' => $vendor->billing_zip_code,
                'shipping_address_line_1' => $vendor->shipping_address_line_1,
                'shipping_address_line_2' => $vendor->shipping_address_line_2,
                'shipping_city' => $vendor->shipping_city,
                'shipping_state' => $vendor->shipping_state,
                'shipping_country' => $vendor->shipping_country,
                'shipping_zip_code' => $vendor->shipping_zip_code,
            ] : null;

            // 2. Create Header
            $grn = GoodsReceivedNote::create([
                'purchase_order_id' => $po?->id,
                'vendor_id' => $vendorId,
                'vendor_meta' => $vendorMeta,
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

            // Preload Master Data
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            if ($po) {
                // Also pull from PO items if needed, but usually we just prefer the fresh DB copies to avoid stale info
                // Actually if a PO item is linked, the GRN inherits its snapshot in standard ERP.
                // For Atom Suit, we'll snapshot the current DB state.
            }

            $itemsDb = \App\Models\Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = \App\Models\TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

            // 3. Create Items
            foreach ($data['items'] as $itemData) {
                $poItem = null;
                if ($po && isset($itemData['purchase_order_item_id'])) {
                    $poItem = $po->items()->find($itemData['purchase_order_item_id']);
                }

                $itemModel = $itemsDb->get($itemData['item_id']);

                $unitPrice = $poItem ? $poItem->unit_price : ($itemData['unit_price'] ?? 0);
                $discountType = $poItem ? $poItem->discount_type : ($itemData['discount_type'] ?? null);
                $discountValue = $poItem ? $poItem->discount_value : ($itemData['discount_value'] ?? 0);
                $taxGroupId = $poItem ? $poItem->tax_group_id : ($itemData['tax_group_id'] ?? null);

                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                // Inherit snapshot from PO if exists, else create new
                $itemMeta = $poItem ? $poItem->item_meta : ($itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null);

                $taxMeta = $poItem ? $poItem->tax_meta : null;
                if (! $taxMeta && $taxModel) {
                    $rates = [];
                    foreach ($taxModel->taxRates as $tr) {
                        $rates[] = [
                            'id' => $tr->id,
                            'name' => $tr->name,
                            'rate' => (float) $tr->rate,
                            'type' => $tr->type->value,
                        ];
                    }
                    $taxMeta = [
                        'id' => $taxModel->id,
                        'name' => $taxModel->name,
                        'rates' => $rates,
                    ];
                }

                $grn->items()->create([
                    'item_id' => $itemData['item_id'],
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? $poItem->description,
                    'quantity_received' => $itemData['quantity_received'],
                    'accepted_quantity' => $itemData['accepted_quantity'],
                    'rejected_quantity' => $itemData['rejected_quantity'] ?? 0,
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // Fire Global Calculator
            $calculator = app(RecalculatePurchaseDocumentTotalsAction::class);
            $calculator->execute($grn);

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
