<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseOrderStatus;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\TaxGroup;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
    public function __construct(protected RecalculatePurchaseDocumentTotalsAction $calculator) {}

    public function handle(array $data, ?User $creator = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $creator) {
            $vendor = Vendor::find($data['vendor_id']);
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

            $po = PurchaseOrder::create([
                'vendor_id' => $data['vendor_id'],
                'vendor_meta' => $vendorMeta,
                'order_number' => $data['order_number'],
                'reference_number' => $data['reference_number'] ?? null,
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => PurchaseOrderStatus::DRAFT,
                'notes' => $data['notes'] ?? null,
                'cost_center_id' => $data['cost_center_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Preload Master Data to prevent N+1 Queries
            $itemIds = array_column($data['items'], 'item_id');
            $taxIds = array_column($data['items'], 'tax_group_id');
            $itemsDb = Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
            $taxesDb = TaxGroup::with('taxRates')->whereIn('id', array_filter($taxIds))->get()->keyBy('id');

            foreach ($data['items'] as $itemData) {
                $itemModel = $itemsDb->get($itemData['item_id']);
                $taxModel = isset($itemData['tax_group_id']) ? $taxesDb->get($itemData['tax_group_id']) : null;

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

                $po->items()->create([
                    'item_id' => $itemData['item_id'],
                    'item_meta' => $itemMeta,
                    'tax_meta' => $taxMeta,
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_type' => $itemData['discount_type'] ?? null,
                    'discount_value' => $itemData['discount_value'] ?? 0,
                    'tax_group_id' => $itemData['tax_group_id'] ?? null,
                ]);
            }

            // Securely calculate exact math based on the snapshotted constraints
            $this->calculator->execute($po);

            return $po;
        });
    }
}
