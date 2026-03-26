<?php

namespace App\Actions\Purchase;

use App\Actions\GeneralLedger\PostDebitNoteToLedgerAction;
use App\Enums\DebitNoteStatus;
use App\Enums\PurchaseInvoiceStatus;
use App\Models\Batch;
use App\Models\DebitNote;
use App\Models\GoodsReceivedNoteItem;
use App\Models\Item;
use App\Models\PurchaseInvoice;
use App\Models\TaxGroup;
use App\Models\User;
use App\Models\Vendor;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateDebitNote
{
    public function __construct(
        protected PostDebitNoteToLedgerAction $postToLedger,
        protected StockMovementService $stockService,
        protected RecalculatePurchaseDocumentTotalsAction $calculator
    ) {}

    public function handle(array $data, ?User $creator = null): DebitNote
    {
        return DB::transaction(function () use ($data, $creator) {
            if (! empty($data['purchase_invoice_id'])) {
                $pi = PurchaseInvoice::findOrFail($data['purchase_invoice_id']);
                if ($pi->status === PurchaseInvoiceStatus::VOIDED) {
                    throw new \Exception('Cannot create a Debit Note against a voided Purchase Invoice.');
                }
            }

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
            $itemsDb = Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

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

                $taxMeta = null;
                if ($taxModel) {
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

                $batchId = $itemData['batch_id'] ?? null;

                // Attempt to auto-find batch if purchase_invoice_id is provided
                if (! $batchId && $debitNote->purchase_invoice_id) {
                    $pi = PurchaseInvoice::find($debitNote->purchase_invoice_id);
                    if ($pi && $pi->goods_received_note_id) {
                        $grnItem = GoodsReceivedNoteItem::where('goods_received_note_id', $pi->goods_received_note_id)
                            ->where('item_id', $itemData['item_id'])
                            ->first();
                        $batchId = $grnItem?->batch_id;
                    }
                }

                if ($batchId && ($itemData['is_stock_returned'] ?? false)) {
                    $batch = Batch::find($batchId);
                    if ($batch && $batch->stockOnHand() < $itemData['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Cannot return {$itemData['quantity']} for item {$itemMeta['name']}. Only {$batch->stockOnHand()} remains in stock from this purchase batch ({$batch->batch_number}).",
                        ]);
                    }
                }

                $debitNote->items()->create([
                    'item_id' => $itemData['item_id'],
                    'batch_id' => $batchId,
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
            $this->calculator->execute($debitNote);

            // 3. Post to General Ledger
            $this->postToLedger->execute($debitNote, (float) $debitNote->total_amount);

            // 4. Update Stock (The service evaluates if the individual items have is_stock_returned true)
            $this->stockService->createStockMovements($debitNote);

            return $debitNote;
        });
    }
}
