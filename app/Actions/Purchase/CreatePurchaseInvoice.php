<?php

namespace App\Actions\Purchase;

use App\Actions\GeneralLedger\PostPurchaseInvoiceToLedgerAction;
use App\Enums\PurchaseInvoiceStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\GoodsReceivedNote;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrder;
use App\Models\TaxGroup;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreatePurchaseInvoice
{
    public function __construct(
        protected PostPurchaseInvoiceToLedgerAction $glPoster,
        protected RecalculatePurchaseDocumentTotalsAction $calculator
    ) {}

    public function handle(?GoodsReceivedNote $grn, ?PurchaseOrder $po, array $data, ?User $creator = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($grn, $po, $data, $creator) {

            $vendorId = $data['vendor_id'] ?? $grn?->vendor_id ?? $po?->vendor_id;
            $vendor = Vendor::find($vendorId);
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

            // 1. Create Header
            $invoice = PurchaseInvoice::create([
                'grn_id' => $grn?->id,
                'purchase_order_id' => $po?->id,
                'vendor_id' => $vendorId,
                'vendor_meta' => $vendorMeta,
                'invoice_number' => $data['invoice_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'posting_date' => $data['posting_date'],
                'due_date' => $data['due_date'],
                'status' => PurchaseInvoiceStatus::POSTED,
                'cost_center_id' => $data['cost_center_id'] ?? $grn?->cost_center_id ?? $po?->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $grn?->warehouse_id ?? $po?->warehouse_id,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Preload Master Data
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            $itemsDb = Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

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

                $itemModel = $itemsDb->get($itemData['item_id']);

                $discountType = $grnItem ? $grnItem->discount_type : ($poItem ? $poItem->discount_type : ($itemData['discount_type'] ?? null));
                $discountValue = $grnItem ? $grnItem->discount_value : ($poItem ? $poItem->discount_value : ($itemData['discount_value'] ?? 0));
                $taxGroupId = $grnItem ? $grnItem->tax_group_id : ($poItem ? $poItem->tax_group_id : ($itemData['tax_group_id'] ?? null));

                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                $itemMeta = $grnItem ? $grnItem->item_meta : ($poItem ? $poItem->item_meta : ($itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null));

                $taxMeta = $grnItem ? $grnItem->tax_meta : ($poItem ? $poItem->tax_meta : null);
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

                $invoice->items()->create([
                    'item_id' => $itemData['item_id'],
                    'goods_received_note_item_id' => $itemData['goods_received_note_item_id'] ?? null,
                    'purchase_order_item_id' => $itemData['purchase_order_item_id'] ?? null,
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? $grnItem->description ?? $poItem->description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // Secure Math
            $this->calculator->execute($invoice);

            // 3. Post to General Ledger
            ($this->glPoster)->handle($invoice);

            // 4. Update PO Status to COMPLETED if fully invoiced
            if ($po) {
                $this->updatePurchaseOrderStatus($po);
            }

            return $invoice;
        });
    }

    protected function updatePurchaseOrderStatus(PurchaseOrder $po): void
    {
        $allItemsInvoiced = true;

        foreach ($po->items as $poItem) {
            $invoicedQty = PurchaseInvoiceItem::where('purchase_order_item_id', $poItem->id)
                ->sum('quantity');

            if ($invoicedQty < $poItem->quantity) {
                $allItemsInvoiced = false;
            }
        }

        if ($allItemsInvoiced) {
            $po->update(['status' => PurchaseOrderStatus::COMPLETED]);
        }
    }
}
