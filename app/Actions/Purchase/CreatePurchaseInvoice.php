<?php

namespace App\Actions\Purchase;

use App\Actions\GeneralLedger\PostPurchaseInvoiceToLedgerAction;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePurchaseInvoice
{
    public function __construct(
        protected PostPurchaseInvoiceToLedgerAction $glPoster
    ) {}

    public function handle(?GoodsReceivedNote $grn, ?\App\Models\PurchaseOrder $po, array $data, ?User $creator = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($grn, $po, $data, $creator) {

            // 1. Create Header
            $invoice = PurchaseInvoice::create([
                'grn_id' => $grn?->id,
                'purchase_order_id' => $po?->id,
                'vendor_id' => $data['vendor_id'] ?? $grn->vendor_id ?? $po->vendor_id,
                'invoice_number' => $data['invoice_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'posting_date' => $data['posting_date'],
                'due_date' => $data['due_date'],
                'status' => PurchaseInvoiceStatus::POSTED,
                'cost_center_id' => $data['cost_center_id'] ?? $grn->cost_center_id ?? $po->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $grn->warehouse_id ?? $po->warehouse_id,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // 2. Create Items
            foreach ($data['items'] as $itemData) {
                // If GRN/PO Item provided, inherit data if needed
                $grnItem = null;
                $poItem = null;
                if ($grn && isset($itemData['goods_received_note_item_id'])) {
                    $grnItem = $grn->items()->find($itemData['goods_received_note_item_id']);
                }
                if ($po && isset($itemData['purchase_order_item_id'])) {
                    $poItem = $po->items()->find($itemData['purchase_order_item_id']);
                }

                $invoice->items()->create([
                    'item_id' => $itemData['item_id'],
                    'goods_received_note_item_id' => $itemData['goods_received_note_item_id'] ?? null,
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'description' => $itemData['description'] ?? $grnItem->description ?? $poItem->description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'tax_group_id' => $itemData['tax_group_id'] ?? null,
                ]);
            }

            // 3. Post to General Ledger
            ($this->glPoster)->handle($invoice);

            // 4. Update PO Status to COMPLETED if fully invoiced
            if ($po) {
                $this->updatePurchaseOrderStatus($po);
            }

            return $invoice;
        });
    }

    protected function updatePurchaseOrderStatus(\App\Models\PurchaseOrder $po): void
    {
        $allItemsInvoiced = true;

        foreach ($po->items as $poItem) {
            $invoicedQty = \App\Models\PurchaseInvoiceItem::where('purchase_order_item_id', $poItem->id)
                ->sum('quantity');

            if ($invoicedQty < $poItem->quantity) {
                $allItemsInvoiced = false;
            }
        }

        if ($allItemsInvoiced) {
            $po->update(['status' => \App\Enums\PurchaseOrderStatus::COMPLETED]);
        }
    }
}
