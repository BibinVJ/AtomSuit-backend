<?php

namespace App\Actions\Purchase;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseOrder
{
    public function __construct(protected RecalculateDocumentTotalsAction $calculator) {}

    public function handle(PurchaseOrder $po, array $data, ?User $updater = null): PurchaseOrder
    {
        if ($po->status !== PurchaseOrderStatus::DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Only draft purchase orders can be updated.',
            ]);
        }

        return DB::transaction(function () use ($po, $data, $updater) {
            $vendorId = $data['vendor_id'] ?? $po->vendor_id;
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

            $po->update([
                'vendor_id' => $vendorId,
                'vendor_meta' => $vendorMeta,
                'order_date' => $data['order_date'] ?? $po->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $po->expected_delivery_date,
                'notes' => $data['notes'] ?? $po->notes,
                'cost_center_id' => $data['cost_center_id'] ?? $po->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $po->warehouse_id,
                'reference_number' => $data['reference_number'] ?? $po->reference_number,
                'updated_by' => $updater?->id,
            ]);

            if (isset($data['items'])) {
                $po->items()->delete();

                $itemIds = array_column($data['items'], 'item_id');
                $taxIds = array_column($data['items'], 'tax_group_id');
                $itemsDb = \App\Models\Item::with(['category', 'unit'])->whereIn('id', $itemIds)->get()->keyBy('id');
                $taxesDb = \App\Models\TaxGroup::whereIn('id', array_filter($taxIds))->get()->keyBy('id');

                foreach ($data['items'] as $itemData) {
                    $itemModel = $itemsDb->get($itemData['item_id']);
                    $taxModel = isset($itemData['tax_group_id']) ? $taxesDb->get($itemData['tax_group_id']) : null;

                    $itemMeta = $itemModel ? [
                        'name' => $itemModel->name,
                        'sku' => $itemModel->sku,
                        'category' => $itemModel->category?->name,
                        'unit' => $itemModel->unit?->name,
                    ] : null;

                    $po->items()->create([
                        'item_id' => $itemData['item_id'],
                        'item_meta' => $itemMeta,
                        'tax_meta' => $taxModel ? ['name' => $taxModel->name, 'rate' => $taxModel->rate] : null,
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'discount_type' => $itemData['discount_type'] ?? null,
                        'discount_value' => $itemData['discount_value'] ?? 0,
                        'tax_group_id' => $itemData['tax_group_id'] ?? null,
                    ]);
                }
            }

            // Sync Header Cache Math
            $this->calculator->execute($po);

            return $po;
        });
    }
}
