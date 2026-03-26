<?php

namespace App\Actions\Sales;

use App\Actions\GeneralLedger\PostDeliveryNoteToLedgerAction;
use App\Enums\DeliveryNoteStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\TaxGroup;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;

class CreateDeliveryNote
{
    public function __construct(
        protected ValidateDnQuantities $validator,
        protected StockMovementService $stockService,
        protected PostDeliveryNoteToLedgerAction $glPoster,
        protected RecalculateSalesDocumentTotalsAction $calculator
    ) {}

    public function handle(?SalesOrder $so, array $data, ?User $creator = null): DeliveryNote
    {
        // 1. Resolve Warehouse and Validate Context
        $warehouseId = $data['warehouse_id'] ?? $so?->warehouse_id;
        $this->validator->validate($so, $data['items'], $warehouseId);

        return DB::transaction(function () use ($so, $data, $creator) {
            $customerId = $data['customer_id'] ?? $so?->customer_id;
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

            // 2. Create Header
            $dn = DeliveryNote::create([
                'sales_order_id' => $so?->id,
                'customer_id' => $customerId,
                'customer_meta' => $customerMeta,
                'dn_number' => $data['dn_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'dispatch_date' => $data['dispatch_date'],
                'status' => DeliveryNoteStatus::DISPATCHED,
                'notes' => $data['notes'] ?? null,
                'cost_center_id' => $data['cost_center_id'] ?? $so?->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $so?->warehouse_id,
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Preload Master Data
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            $itemsDb = Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

            // 3. Create Items
            foreach ($data['items'] as $itemData) {
                $soItem = null;
                if ($so && isset($itemData['sales_order_item_id'])) {
                    $soItem = $so->items()->find($itemData['sales_order_item_id']);
                }

                $itemModel = $itemsDb->get($itemData['item_id']);

                $unitPrice = $soItem ? $soItem->unit_price : ($itemData['unit_price'] ?? 0);
                $discountType = $soItem ? $soItem->discount_type : ($itemData['discount_type'] ?? null);
                $discountValue = $soItem ? $soItem->discount_value : ($itemData['discount_value'] ?? 0);
                $taxGroupId = $soItem ? $soItem->tax_group_id : ($itemData['tax_group_id'] ?? null);

                $taxModel = $taxGroupId ? $taxesDb->get($taxGroupId) : null;

                // Inherit snapshot
                $itemMeta = $soItem ? $soItem->item_meta : ($itemModel ? [
                    'name' => $itemModel->name,
                    'sku' => $itemModel->sku,
                    'category' => $itemModel->category?->name,
                    'unit' => $itemModel->unit?->name,
                ] : null);

                $taxMeta = $soItem ? $soItem->tax_meta : null;
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

                $dn->items()->create([
                    'item_id' => $itemData['item_id'],
                    'sales_order_item_id' => $itemData['sales_order_item_id'] ?? null,
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? $soItem?->description,
                    'dispatched_quantity' => $itemData['dispatched_quantity'],
                    'unit_price' => $unitPrice,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                    'tax_group_id' => $taxGroupId,
                ]);
            }

            // Fire Global Calculator
            $this->calculator->execute($dn);

            // 4. Update Stock
            $this->stockService->createStockMovements($dn);

            // 5. Conditional COGS Posting
            $cogsAccount = Setting::getValue('default_cogs_account');
            if ($cogsAccount) {
                $this->glPoster->handle($dn);
            }

            // 6. Update SO Status
            if ($so) {
                $this->updateSalesOrderStatus($so);
            }

            return $dn;
        });
    }

    protected function updateSalesOrderStatus(SalesOrder $so): void
    {
        $allItemsDelivered = true;
        $anyItemsDelivered = false;

        foreach ($so->items as $soItem) {
            $deliveredQty = DeliveryNoteItem::where('sales_order_item_id', $soItem->id)
                ->sum('dispatched_quantity');

            if ($deliveredQty > 0) {
                $anyItemsDelivered = true;
            }

            if ($deliveredQty < $soItem->quantity) {
                $allItemsDelivered = false;
            }
        }

        if ($allItemsDelivered) {
            $so->update(['status' => SalesOrderStatus::DELIVERED]);
        } elseif ($anyItemsDelivered) {
            $so->update(['status' => SalesOrderStatus::PARTIALLY_DELIVERED]);
        }
    }
}
