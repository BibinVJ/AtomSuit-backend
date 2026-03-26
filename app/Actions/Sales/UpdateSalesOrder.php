<?php

namespace App\Actions\Sales;

use App\Enums\SalesOrderStatus;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\TaxGroup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSalesOrder
{
    public function __construct(protected RecalculateSalesDocumentTotalsAction $calculator) {}

    public function handle(SalesOrder $so, array $data, ?User $updater = null): SalesOrder
    {
        // Only allow updates if not delivered/completed (simplified for now)
        if (in_array($so->status, [SalesOrderStatus::COMPLETED, SalesOrderStatus::DELIVERED])) {
            throw ValidationException::withMessages([
                'status' => 'Completed or Delivered sales orders cannot be updated.',
            ]);
        }

        return DB::transaction(function () use ($so, $data, $updater) {
            $customerId = $data['customer_id'] ?? $so->customer_id;
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

            $so->update([
                'customer_id' => $customerId,
                'customer_meta' => $customerMeta,
                'order_date' => $data['order_date'] ?? $so->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $so->expected_delivery_date,
                'notes' => $data['notes'] ?? $so->notes,
                'cost_center_id' => $data['cost_center_id'] ?? $so->cost_center_id,
                'warehouse_id' => $data['warehouse_id'] ?? $so->warehouse_id,
                'reference_number' => $data['reference_number'] ?? $so->reference_number,
                'updated_by' => $updater?->id,
            ]);

            if (isset($data['items'])) {
                $so->items()->delete();

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

                    $so->items()->create([
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
            }

            // Sync Header Cache Math
            $this->calculator->execute($so);

            return $so;
        });
    }
}
