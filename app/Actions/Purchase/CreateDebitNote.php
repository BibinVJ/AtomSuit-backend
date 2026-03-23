<?php

namespace App\Actions\Purchase;

use App\Actions\GeneralLedger\PostDebitNoteToLedgerAction;
use App\Actions\StockMovement\CreateDebitNoteStockMovementsAction;
use App\Enums\DebitNoteStatus;
use App\Models\DebitNote;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreateDebitNote
{
    public function __construct(
        protected PostDebitNoteToLedgerAction $postToLedger,
        protected CreateDebitNoteStockMovementsAction $stockAction
    ) {}

    public function handle(array $data, ?User $creator = null): DebitNote
    {
        return DB::transaction(function () use ($data, $creator) {

            $vendor = Vendor::findOrFail($data['vendor_id']);
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
            $debitNote = DebitNote::create([
                'vendor_id' => $vendor->id,
                'vendor_meta' => $vendorMeta,
                'purchase_invoice_id' => $data['purchase_invoice_id'] ?? null,
                'debit_note_number' => $data['debit_note_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'date' => $data['date'],
                'status' => DebitNoteStatus::POSTED,
                'notes' => $data['notes'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'cost_center_id' => $data['cost_center_id'],
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Preload Master Data
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            $itemsDb = \App\Models\Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = \App\Models\TaxGroup::whereIn('id', array_filter($taxIds))->get()->keyBy('id');

            // 2. Process Items
            foreach ($data['items'] as $itemData) {
                $itemModel = $itemsDb->get($itemData['item_id']);
                $taxGroupId = $itemData['tax_group_id'] ?? null;
                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                $itemMeta = $itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null;
                $taxMeta = $taxModel ? ['name' => $taxModel->name, 'rate' => $taxModel->rate] : null;

                $debitNote->items()->create([
                    'item_id' => $itemData['item_id'],
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $itemData['discount_type'] ?? null,
                    'discount_value' => $itemData['discount_value'] ?? 0,
                    'tax_group_id' => $taxGroupId,
                    'description' => $itemData['description'] ?? null,
                    'is_stock_returned' => $itemData['is_stock_returned'] ?? false,
                ]);
            }

            // Secure Math Cache
            app(\App\Actions\Purchase\RecalculateDocumentTotalsAction::class)->execute($debitNote);

            // 3. Post to General Ledger
            $this->postToLedger->execute($debitNote, (float) $debitNote->total_amount);

            // 4. Update Stock (The action evaluates if the individual items have is_stock_returned true)
            $this->stockAction->execute($debitNote);

            return $debitNote;
        });
    }
}
