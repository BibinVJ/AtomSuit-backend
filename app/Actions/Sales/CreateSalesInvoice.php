<?php

namespace App\Actions\Sales;

use App\Actions\GeneralLedger\PostSalesInvoiceToLedgerAction;
use App\Enums\SalesInvoiceStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\SalesOrder;
use App\Models\TaxGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSalesInvoice
{
    public function __construct(
        protected PostSalesInvoiceToLedgerAction $glPoster,
        protected RecalculateSalesDocumentTotalsAction $calculator
    ) {}

    public function handle(?DeliveryNote $dn, ?SalesOrder $so, array $data, ?User $creator = null): SalesInvoice
    {
        return DB::transaction(function () use ($dn, $so, $data, $creator) {

            $customerId = $data['customer_id'] ?? $dn?->customer_id ?? $so?->customer_id;
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
            $invoice = SalesInvoice::create([
                'delivery_note_id' => $dn?->id,
                'sales_order_id' => $so?->id,
                'customer_id' => $customerId,
                'customer_meta' => $customerMeta,
                'invoice_number' => $data['invoice_number'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => SalesInvoiceStatus::POSTED,
                'cost_center_id' => $data['cost_center_id'] ?? $dn?->cost_center_id ?? $so?->cost_center_id,
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
                $dnItem = null;
                $soItem = null;
                if ($dn && isset($itemData['delivery_note_item_id'])) {
                    $dnItem = $dn->items()->find($itemData['delivery_note_item_id']);
                }
                if ($so && isset($itemData['sales_order_item_id'])) {
                    $soItem = $so->items()->find($itemData['sales_order_item_id']);
                }

                $itemModel = $itemsDb->get($itemData['item_id']);

                $unitPrice = $dnItem ? $dnItem->unit_price : ($soItem ? $soItem->unit_price : ($itemData['unit_price'] ?? 0));
                $discountType = $dnItem ? $dnItem->discount_type : ($soItem ? $soItem->discount_type : ($itemData['discount_type'] ?? null));
                $discountValue = $dnItem ? $dnItem->discount_value : ($soItem ? $soItem->discount_value : ($itemData['discount_value'] ?? 0));
                $taxGroupId = $dnItem ? $dnItem->tax_group_id : ($soItem ? $soItem->tax_group_id : ($itemData['tax_group_id'] ?? null));

                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                $itemMeta = $dnItem ? $dnItem->item_meta : ($soItem ? $soItem->item_meta : ($itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null));

                $taxMeta = $dnItem ? $dnItem->tax_meta : ($soItem ? $soItem->tax_meta : null);
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
                    'delivery_note_item_id' => $itemData['delivery_note_item_id'] ?? null,
                    'sales_order_item_id' => $itemData['sales_order_item_id'] ?? null,
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? $dnItem?->description ?? $soItem?->description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // Secure Math
            $this->calculator->execute($invoice);

            // 3. Post to General Ledger
            ($this->glPoster)->handle($invoice);

            // 4. Update SO Status to COMPLETED if fully invoiced
            if ($so) {
                $this->updateSalesOrderStatus($so);
            }

            return $invoice;
        });
    }

    protected function updateSalesOrderStatus(SalesOrder $so): void
    {
        $allItemsInvoiced = true;

        foreach ($so->items as $soItem) {
            $invoicedQty = SalesInvoiceItem::where('sales_order_item_id', $soItem->id)
                ->sum('quantity');

            if ($invoicedQty < $soItem->quantity) {
                $allItemsInvoiced = false;
            }
        }

        if ($allItemsInvoiced) {
            $so->update(['status' => SalesOrderStatus::COMPLETED]);
        }
    }
}
