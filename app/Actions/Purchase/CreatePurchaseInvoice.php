<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseInvoiceStatus;
use App\Models\GoodsReceivedNote;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePurchaseInvoice
{
    public function __construct(
        protected PostInvoiceToLedger $glPoster
    ) {}

    public function handle(GoodsReceivedNote $grn, array $data, ?User $creator = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($grn, $data, $creator) {

            // 1. Create Header
            $invoice = PurchaseInvoice::create([
                'grn_id' => $grn->id,
                'vendor_id' => $grn->vendor_id,
                'invoice_number' => $data['invoice_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'posting_date' => $data['posting_date'],
                'due_date' => $data['due_date'],
                'status' => PurchaseInvoiceStatus::POSTED,
                'cost_center_id' => $grn->cost_center_id,
                'warehouse_id' => $grn->warehouse_id,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // 2. Create Items
            foreach ($data['items'] as $itemData) {
                $invoice->items()->create([
                    'item_id' => $itemData['item_id'],
                    'goods_received_note_item_id' => $itemData['goods_received_note_item_id'] ?? null,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'tax_group_id' => $itemData['tax_group_id'] ?? null,
                ]);
            }

            // 3. Post to General Ledger
            ($this->glPoster)->handle($invoice);

            return $invoice;
        });
    }
}
