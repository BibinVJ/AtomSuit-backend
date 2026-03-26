<?php

namespace App\Actions\Sales;

use App\Actions\GeneralLedger\PostCreditNoteToLedgerAction;
use App\Enums\CreditNoteStatus;
use App\Enums\SalesInvoiceStatus;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\DeliveryNoteItem;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\StockMovement;
use App\Models\TaxGroup;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreateCreditNote
{
    public function __construct(
        protected PostCreditNoteToLedgerAction $glPoster,
        protected RecalculateSalesDocumentTotalsAction $calculator,
        protected StockMovementService $stockService
    ) {}

    public function handle(?SalesInvoice $invoice, array $data, ?User $creator = null): CreditNote
    {
        return DB::transaction(function () use ($invoice, $data, $creator) {
            if ($invoice && $invoice->status === SalesInvoiceStatus::VOIDED) {
                throw new \Exception('Cannot create a Credit Note against a voided Sales Invoice.');
            }

            $customerId = $data['customer_id'] ?? $invoice?->customer_id;
            $customer = Customer::find($customerId);
            $customerMeta = $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'billing_address_line_1' => $customer->billing_address_line_1,
                'billing_address_line_2' => $customer->billing_address_line_2,
                'billing_city' => $customer->billing_city,
                'billing_state' => $customer->billing_state,
                'billing_country' => $customer->billing_country,
                'billing_zip_code' => $customer->billing_zip_code,
                'shipping_address_line_1' => $customer->shipping_address_line_1,
                'shipping_address_line_2' => $customer->shipping_address_line_2,
                'shipping_city' => $customer->shipping_city,
                'shipping_state' => $customer->shipping_state,
                'shipping_country' => $customer->shipping_country,
                'shipping_zip_code' => $customer->shipping_zip_code,
            ] : null;

            // 1. Create Header
            $cn = CreditNote::create([
                'sales_invoice_id' => $invoice?->id,
                'customer_id' => $customerId,
                'customer_meta' => $customerMeta,
                'credit_note_number' => $data['credit_note_number'],
                'credit_note_date' => $data['credit_note_date'],
                'status' => CreditNoteStatus::POSTED,
                'is_stock_returned' => $data['is_stock_returned'] ?? false,
                'cost_center_id' => $data['cost_center_id'] ?? $invoice?->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $invoice?->warehouse_id,
                'notes' => $data['notes'] ?? null,
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
                $siItem = null;
                if ($invoice && isset($itemData['sales_invoice_item_id'])) {
                    $siItem = $invoice->items()->find($itemData['sales_invoice_item_id']);
                }

                $itemModel = $itemsDb->get($itemData['item_id']);

                $unitPrice = $siItem ? $siItem->unit_price : ($itemData['unit_price'] ?? 0);
                $discountType = $siItem ? $siItem->discount_type : ($itemData['discount_type'] ?? null);
                $discountValue = $siItem ? $siItem->discount_value : ($itemData['discount_value'] ?? 0);
                $taxGroupId = $siItem ? $siItem->tax_group_id : ($itemData['tax_group_id'] ?? null);

                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                $itemMeta = $siItem ? $siItem->item_meta : ($itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null);

                $taxMeta = $siItem ? $siItem->tax_meta : null;
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

                $batchId = $itemData['batch_id'] ?? null;

                // Auto-resolve batch from original sale
                if (! $batchId && $siItem && $siItem->delivery_note_item_id) {
                    $dnItem = DeliveryNoteItem::find($siItem->delivery_note_item_id);
                    if ($dnItem) {
                        // Find the batch used in the delivery note
                        $movement = StockMovement::where('source_type', \App\Models\DeliveryNote::class)
                            ->where('source_id', $dnItem->delivery_note_id)
                            ->where('item_id', $itemData['item_id'])
                            ->where('quantity', '<', 0)
                            ->first();
                        $batchId = $movement?->batch_id;
                    }
                }

                $cn->items()->create([
                    'item_id' => $itemData['item_id'],
                    'batch_id' => $batchId,
                    'sales_invoice_item_id' => $itemData['sales_invoice_item_id'] ?? null,
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? $siItem?->description,
                    'returned_quantity' => $itemData['returned_quantity'],
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // Fire Global Calculator
            $this->calculator->execute($cn);

            // 3. Post to General Ledger
            ($this->glPoster)->handle($cn);

            // 4. Optionally Update Stock (Reversal)
            if ($cn->is_stock_returned) {
                $this->stockService->createStockMovements($cn);
            }

            return $cn;
        });
    }
}
